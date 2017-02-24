<?php

namespace BenTools\ETL\Runner;

use BenTools\ETL\Context\ContextElementFactoryInterface;
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Context\KeyValueFactory;
use BenTools\ETL\Event\ETLEvent;
use BenTools\ETL\Event\EventDispatcher\EventDispatcherInterface;
use BenTools\ETL\Event\EventDispatcher\NullEventDispatcher;
use BenTools\ETL\Event\Events;
use BenTools\ETL\Loader\FlushableLoaderInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Runner implements RunnerInterface {

    use LoggerAwareTrait;

    /**
     * @var bool
     */
    private $flush = true;

    /**
     * @var ContextElementFactoryInterface
     */
    protected $factory;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Runner constructor.
     * @param ContextElementFactoryInterface|null $factory
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(ContextElementFactoryInterface $factory = null,
                                LoggerInterface $logger = null,
                                EventDispatcherInterface $eventDispatcher = null) {
        $this->factory = $factory;
        $this->logger = $logger ?? new NullLogger();
        $this->eventDispatcher = $eventDispatcher ?? new NullEventDispatcher();
    }

    /**
     * @return ContextElementFactoryInterface
     */
    public function getFactory(): ContextElementFactoryInterface {
        return $this->factory ?? new KeyValueFactory();
    }

    /**
     * @param ContextElementFactoryInterface $factory
     * @return $this - Provides Fluent Interface
     */
    public function setFactory(ContextElementFactoryInterface $factory) {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __invoke($extractor, $transformer, $loader) {

        $this->validateExtractor($extractor);
        $this->validateTransformer($transformer);
        $this->validateLoader($loader);

        foreach ($extractor AS $key => $value) {

            // Extract and create element
            $element = $this->extract($key, $value);

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

        }

        // Flush remaining data
        $this->flush($loader);

    }

    /**
     * @param $key
     * @param $value
     * @return ContextElementInterface
     */
    private function extract($key, $value): ContextElementInterface {
        $this->logger->info(sprintf('Extracting key %s...', $key));
        $element = $this->getFactory()->createContext($key, $value);
        $this->logger->debug(sprintf('Key %s extracted.', $key), [
            'id'   => $element->getIdentifier(),
            'data' => $element->getExtractedData(),
        ]);
        $this->eventDispatcher->trigger(new ETLEvent(Events::AFTER_EXTRACT, $element));
        return $element;
    }

    /**
     * @param $transform
     * @param ContextElementInterface $element
     */
    private function transform($transform, ContextElementInterface $element) {
        $identifier = $element->getIdentifier();
        $this->logger->info(sprintf('Transforming key %s...', $identifier));
        $transform($element);
        $this->logger->debug(sprintf('Key %s transformed.', $identifier), [
            'id'   => $element->getIdentifier(),
            'data' => $element->getExtractedData(),
        ]);
        $this->eventDispatcher->trigger(new ETLEvent(Events::AFTER_TRANSFORM, $element));
    }

    /**
     * @param $load
     * @param ContextElementInterface $element
     */
    private function load($load, ContextElementInterface $element) {
        $identifier = $element->getIdentifier();
        $this->logger->info(sprintf('Loading key %s...', $identifier));
        $load($element);
        $this->logger->debug(sprintf('Key %s loaded.', $identifier), [
            'id'   => $element->getIdentifier(),
        ]);
        $this->eventDispatcher->trigger(new ETLEvent(Events::AFTER_LOAD, $element));
    }

    /**
     * @param $loader
     */
    private function flush($loader) {
        if ($this->shouldFlush() && $loader instanceof FlushableLoaderInterface) {
            $loader->flush();
        }
        $this->eventDispatcher->trigger(new ETLEvent(Events::AFTER_FLUSH));
    }

    /**
     * @param $key
     */
    private function skip($key) {
        $this->logger->info(sprintf('Skipping key %s...', $key));
    }

    /**
     * @param $key
     */
    private function stop($key, ContextElementInterface $element) {
        $this->logger->info(sprintf('Stopping on key %s...', $key));
        $this->flush = $element->shouldFlush();
    }

    /**
     * @return bool
     */
    private function shouldFlush(): bool {
        return $this->flush;
    }

    /**
     * @param iterable|\Generator|\Traversable|array $extractor
     */
    private function validateExtractor($extractor) {
        if (!is_array($extractor) && !$extractor instanceof \Traversable && !$extractor instanceof \Generator) {
            throw new \InvalidArgumentException("The extractor should be iterable.");
        }
    }

    /**
     * @param callable $transformer
     */
    private function validateTransformer($transformer) {
        if (!is_callable($transformer)) {
            throw new \InvalidArgumentException("The transformer should be callable.");
        }
    }

    /**
     * @param callable $loader
     */
    private function validateLoader($loader) {
        if (!is_callable($loader)) {
            throw new \InvalidArgumentException("The loader should be callable.");
        }
    }


}