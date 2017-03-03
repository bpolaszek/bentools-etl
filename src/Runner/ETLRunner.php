<?php

namespace BenTools\ETL\Runner;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\ContextElementEvent;
use BenTools\ETL\Event\EventDispatcher\EventDispatcherInterface;
use BenTools\ETL\Event\EventDispatcher\NullEventDispatcher;
use BenTools\ETL\Event\ETLEvents;
use BenTools\ETL\Event\ETLEvent;
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
        $this->eventDispatcher = $eventDispatcher ?? new NullEventDispatcher();
    }

    /**
     * @inheritDoc
     */
    public function __invoke(iterable $items, callable $extractor, callable $transformer = null, callable $loader)
    {

        $this->start();

        foreach ($items as $key => $value) {
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
            $this->load($loader, $element)->flush($loader, false);
        }

        // Flush remaining data
        $this->flush($loader, true);
        $this->end();
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
        /**
         * @var ContextElementInterface $element
         */
        $element = $extract($key, $value);
        $this->logger->debug(
            sprintf('Key %s extracted.', $key),
            [
            'id'   => $element->getId(),
            'data' => $element->getData(),
            ]
        );
        $this->eventDispatcher->trigger(new ContextElementEvent(ETLEvents::AFTER_EXTRACT, $element));
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
            $transform($element);
            $this->logger->debug(
                sprintf('Key %s transformed.', $identifier),
                [
                    'id'   => $element->getId(),
                    'data' => $element->getData(),
                ]
            );
            $this->eventDispatcher->trigger(new ContextElementEvent(ETLEvents::AFTER_TRANSFORM, $element));
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
        $load($element);
        $this->logger->debug(
            sprintf('Key %s loaded.', $identifier),
            [
            'id'   => $element->getId(),
            ]
        );
        $this->eventDispatcher->trigger(new ContextElementEvent(ETLEvents::AFTER_LOAD, $element));
        return $this;
    }

    /**
     * @param $loader
     */
    private function flush($loader, bool $forceFlush = false): void
    {
        if ($this->shouldFlush()
            && ($loader instanceof FlushableLoaderInterface
                && (true === $forceFlush || $loader->shouldFlushAfterLoad()))) {
            $loader->flush();
        }
        $this->eventDispatcher->trigger(new ETLEvent(ETLEvents::AFTER_FLUSH));
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
     */
    private function stop($key, ContextElementInterface $element)
    {
        $this->logger->info(sprintf('Stopping on key %s...', $key));
        $this->flush = $element->shouldFlush();
    }

    /**
     * @return bool
     */
    private function shouldFlush(): bool
    {
        return $this->flush;
    }
}
