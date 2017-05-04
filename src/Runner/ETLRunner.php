<?php

namespace BenTools\ETL\Runner;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\ContextElementEvent;
use BenTools\ETL\Event\EventDispatcher\ETLEventDispatcher;
use BenTools\ETL\Event\EventDispatcher\EventDispatcherInterface;
use BenTools\ETL\Event\ETLEvents;
use BenTools\ETL\Event\ETLEvent;
use BenTools\ETL\Event\ExtractExceptionEvent;
use BenTools\ETL\Exception\ExtractionFailedException;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Loader\FlushableLoaderInterface;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Transformer\TransformerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ETLRunner implements ETLRunnerInterface
{

    use LoggerAwareTrait;

    /**
     * @var bool
     */
    private $flush = true;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    private $start = 0.0;
    private $end = 0.0;

    /**
     * ETLRunner constructor.
     *
     * @param LoggerInterface               $logger
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(
        LoggerInterface $logger = null,
        EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->eventDispatcher = $eventDispatcher ?? new ETLEventDispatcher();
    }

    /**
     * @inheritDoc
     */
    public function __invoke(iterable $items, callable $extractor, callable $transformer = null, callable $loader)
    {

        $this->start();

        foreach ($items as $key => $value) {
            try {
                // Extract and create element
                $element = $this->extract($extractor, $key, $value);

                if ($element->shouldSkip()) {
                    $this->skip($key);
                    continue;
                }
                if ($element->shouldStop()) {
                    $this->stop($key, $element);
                    break;
                }

                // Transform element
                $this->transform($transformer, $element);

                if ($element->shouldSkip()) {
                    $this->skip($key);
                    continue;
                }
                if ($element->shouldStop()) {
                    $this->stop($key, $element);
                    break;
                }

                // Load element
                $this->load($loader, $element);

                if ($element->shouldStop()) {
                    $this->stop($key, $element);
                    break;
                }

                // Flush if necessary (the loader will decide)
                $this->flush($loader, false);
            } catch (ExtractionFailedException $exception) { // If extraction failed

                // We may prevent the ETL to flush.
                if (false === $exception->shouldFlush()) {
                    $this->flush = false;
                }

                // And/or we could stop right now.
                if ($exception->shouldStop()) {
                    $this->stop($key);
                    break;
                }

                // If the exception should be blocking, throw it
                if (!$exception->shouldIgnore()) {
                    throw $exception;
                }
            }
        }

        // Flush remaining data
        $this->flush($loader, true);

        $this->end();
    }

    /**
     * Shortcut to ETLEvents::AFTER_EXTRACT listener creation.
     *
     * @param callable $callback
     */
    public function onExtract(callable $callback): void
    {
        $this->eventDispatcher->addListener(ETLEvents::AFTER_EXTRACT, $callback);
    }

    /**
     * Shortcut to ETLEvents::ON_EXTRACT_EXCEPTION listener creation.
     *
     * @param callable $callback
     */
    public function onExtractException(callable $callback): void
    {
        $this->eventDispatcher->addListener(ETLEvents::ON_EXTRACT_EXCEPTION, $callback);
    }

    /**
     * Shortcut to ETLEvents::AFTER_TRANSFORM listener creation.
     *
     * @param callable $callback
     */
    public function onTransform(callable $callback): void
    {
        $this->eventDispatcher->addListener(ETLEvents::AFTER_TRANSFORM, $callback);
    }

    /**
     * Shortcut to ETLEvents::ON_TRANSFORM_EXCEPTION listener creation.
     *
     * @param callable $callback
     */
    public function onTransformException(callable $callback): void
    {
        $this->eventDispatcher->addListener(ETLEvents::ON_TRANSFORM_EXCEPTION, $callback);
    }

    /**
     * Shortcut to ETLEvents::AFTER_LOAD listener creation.
     *
     * @param callable $callback
     */
    public function onLoad(callable $callback): void
    {
        $this->eventDispatcher->addListener(ETLEvents::AFTER_LOAD, $callback);
    }

    /**
     * Shortcut to ETLEvents::ON_LOAD_EXCEPTION listener creation.
     *
     * @param callable $callback
     */
    public function onLoadException(callable $callback): void
    {
        $this->eventDispatcher->addListener(ETLEvents::ON_LOAD_EXCEPTION, $callback);
    }

    /**
     * Shortcut to ETLEvents::AFTER_FLUSH listener creation.
     *
     * @param callable $callback
     */
    public function onFlush(callable $callback): void
    {
        $this->eventDispatcher->addListener(ETLEvents::AFTER_FLUSH, $callback);
    }

    /**
     * Shortcut to ETLEvents::ON_FLUSH_EXCEPTION listener creation.
     *
     * @param callable $callback
     */
    public function onFlushException(callable $callback): void
    {
        $this->eventDispatcher->addListener(ETLEvents::ON_FLUSH_EXCEPTION, $callback);
    }

    private function reset()
    {
        $this->start = 0.0;
        $this->end = 0.0;
        $this->flush = true;
    }

    private function start()
    {
        $this->reset();
        $this->start = microtime(true);
        $this->eventDispatcher->trigger(new ETLEvent(ETLEvents::START));
        $this->logger->info('Starting ETL...');
    }

    private function end()
    {
        $this->end = microtime(true);
        $this->eventDispatcher->trigger(new ETLEvent(ETLEvents::END));
        $this->logger->info(sprintf('ETL completed in %ss', round($this->end - $this->start, 3)));
    }

    /**
     * @param callable|ExtractorInterface $extract
     * @param $key
     * @param $value
     * @return ContextElementInterface
     */
    private function extract(callable $extract, $key, $value): ContextElementInterface
    {
        $this->logger->info(sprintf('Extracting key %s...', $key));

        try {

            /**
             * @var ContextElementInterface $element
             */
            $element = $extract($key, $value);
            $this->eventDispatcher->trigger(new ContextElementEvent(ETLEvents::AFTER_EXTRACT, $element));
        } catch (\Throwable $exception) {
            $extractionFailedException = new ExtractionFailedException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
            $event = new ExtractExceptionEvent($extractionFailedException, $key, $value);
            $this->eventDispatcher->trigger($event);
            throw $extractionFailedException;
        }
        $this->logger->debug(
            sprintf('Key %s extracted.', $key),
            [
            'id'   => $element->getId(),
            'data' => $element->getData(),
            ]
        );

        return $element;
    }

    /**
     * @param callable|TransformerInterface $transform
     * @param ContextElementInterface       $element
     */
    private function transform(callable $transform = null, ContextElementInterface $element): void
    {
        if (null !== $transform) {
            $identifier = $element->getId();
            $this->logger->info(sprintf('Transforming key %s...', $identifier));
            try {
                $transform($element);
                $this->eventDispatcher->trigger(new ContextElementEvent(ETLEvents::AFTER_TRANSFORM, $element));
            } catch (\Throwable $exception) {
                $event = new ContextElementEvent(ETLEvents::ON_TRANSFORM_EXCEPTION, $element);
                $event->setException($exception);
                $this->eventDispatcher->trigger($event); // Event listeners may handle and remove the exception
                if ($event->hasException()) {
                    throw $event->getException(); // Otherwise, throw it
                }
            }
            $this->logger->debug(
                sprintf('Key %s transformed.', $identifier),
                [
                    'id'   => $element->getId(),
                    'data' => $element->getData(),
                ]
            );
        }
    }

    /**
     * @param callable|LoaderInterface $load
     * @param ContextElementInterface  $element
     * @return $this
     */
    private function load(callable $load, ContextElementInterface $element): self
    {
        $identifier = $element->getId();
        $this->logger->info(sprintf('Loading key %s...', $identifier));
        try {
            $load($element);
            $this->eventDispatcher->trigger(new ContextElementEvent(ETLEvents::AFTER_LOAD, $element));
        } catch (\Throwable $exception) {
            $event = new ContextElementEvent(ETLEvents::ON_LOAD_EXCEPTION, $element);
            $event->setException($exception);
            $this->eventDispatcher->trigger($event); // Event listeners may handle and remove the exception
            if ($event->hasException()) {
                throw $event->getException(); // Otherwise, throw it
            }
        }
        $this->logger->debug(
            sprintf('Key %s loaded.', $identifier),
            [
            'id'   => $element->getId(),
            ]
        );
        return $this;
    }

    /**
     * @param $loader
     */
    private function flush($loader, bool $forceFlush = false): void
    {
        if ($this->shouldFlush()
            && ($loader instanceof FlushableLoaderInterface
            && (true === $forceFlush || $loader->shouldFlushAfterLoad()))
        ) {
            $this->logger->info('Flushing elements...');
            $loader->flush();
            $this->eventDispatcher->trigger(new ETLEvent(ETLEvents::AFTER_FLUSH));
        }
    }

    /**
     * @param $key
     */
    private function skip($key)
    {
        $this->logger->info(sprintf('Skipping key %s...', $key));
    }

    /**
     * @param $key
     * @param ContextElementInterface|null $element
     */
    private function stop($key, ContextElementInterface $element = null)
    {
        $this->logger->info(sprintf('Stopping on key %s...', $key));
        if (null !== $element) {
            $this->flush = $element->shouldFlush();
        }
    }

    /**
     * @return bool
     */
    private function shouldFlush(): bool
    {
        return $this->flush;
    }
}
