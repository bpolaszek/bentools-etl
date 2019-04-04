<?php

namespace BenTools\ETL;

use BenTools\ETL\EventDispatcher\EtlEvents;
use BenTools\ETL\EventDispatcher\Event\EndProcessEvent;
use BenTools\ETL\EventDispatcher\Event\FlushEvent;
use BenTools\ETL\EventDispatcher\Event\ItemEvent;
use BenTools\ETL\EventDispatcher\Event\ItemExceptionEvent;
use BenTools\ETL\EventDispatcher\Event\RollbackEvent;
use BenTools\ETL\EventDispatcher\Event\StartProcessEvent;
use BenTools\ETL\EventDispatcher\EventDispatcher;
use BenTools\ETL\Exception\EtlException;
use BenTools\ETL\Loader\NullLoader;
use Psr\EventDispatcher\EventDispatcherInterface;

final class Etl
{
    /**
     * @var callable
     */
    private $extract;

    /**
     * @var callable|null
     */
    private $transform;

    /**
     * @var callable|null
     */
    private $init;

    /**
     * @var callable
     */
    private $load;

    /**
     * @var callable|null
     */
    private $flush;

    /**
     * @var callable|null
     */
    private $rollback;

    /**
     * @var int
     */
    private $flushEvery;

    /**
     * @var bool
     */
    private $shouldSkip;

    /**
     * @var bool
     */
    private $shouldStop;

    /**
     * @var bool
     */
    private $shouldRollback;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Etl constructor.
     */
    public function __construct(
        ?callable $extract = null,
        ?callable $transform = null,
        ?callable $load = null,
        ?callable $initLoader = null,
        ?callable $flush = null,
        ?callable $rollback = null,
        ?int $flushEvery = null,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->extract = $extract;
        $this->transform = $transform ?? self::defaultTransformer();
        $this->init = $initLoader;
        $this->load = $load ?? new NullLoader();
        $this->flush = $flush;
        $this->rollback = $rollback;
        $this->flushEvery = null !== $flushEvery ? max(1, $flushEvery) : null;
        $this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();
    }

    /**
     * Run the ETL on the given input.
     *
     * @param $data
     * @throws EtlException
     */
    public function process($data): void
    {
        $flushCounter = $totalCounter = 0;
        $this->start();

        foreach ($this->extract($data) as $key => $item) {
            if ($this->shouldSkip) {
                $this->skip($item, $key);
                continue;
            }

            if ($this->shouldStop) {
                $this->stop($item, $key);
                break;
            }

            $transformed = $this->transform($item, $key);

            if ($this->shouldSkip) {
                $this->skip($item, $key);
                continue;
            }

            if ($this->shouldStop) {
                $this->stop($item, $key);
                break;
            }

            $flushCounter++;
            $totalCounter++;

            if (1 === $totalCounter) {
                $this->initLoader();
            }

            $flush = (null === $this->flushEvery ? false : (0 === ($totalCounter % $this->flushEvery)));
            $this->load($transformed(), $item, $key, $flush, $flushCounter, $totalCounter);
        }

        $this->end($flushCounter, $totalCounter);
    }

    private function start(): void
    {
        $this->reset();
        $this->eventDispatcher->dispatch(new StartProcessEvent($this));
    }

    /**
     * Mark the current item to be skipped.
     */
    public function skipCurrentItem(): void
    {
        $this->shouldSkip = true;
    }

    /**
     * Process item skip.
     *
     * @param $item
     * @param $key
     */
    private function skip($item, $key): void
    {
        $this->shouldSkip = false;
        $this->eventDispatcher->dispatch(new ItemEvent(EtlEvents::SKIP, $item, $key, $this));
    }

    /**
     * Ask the ETl to stop.
     *
     * @param bool $rollback - if the loader should rollback instead of flushing.
     */
    public function stopProcessing(bool $rollback = false): void
    {
        $this->shouldStop = true;
        $this->shouldRollback = $rollback;
    }

    /**
     * @param     $item
     * @param     $key
     */
    private function stop($item, $key): void
    {
        $this->eventDispatcher->dispatch(new ItemEvent(EtlEvents::STOP, $item, $key, $this));
    }

    /**
     * Reset the ETL.
     */
    private function reset(): void
    {
        $this->shouldSkip = false;
        $this->shouldStop = false;
        $this->shouldRollback = false;
    }

    /**
     * Extract data.
     *
     * @param $data
     * @return iterable
     * @throws EtlException
     */
    private function extract($data): iterable
    {
        $items = null === $this->extract ? $data : ($this->extract)($data, $this);

        if (null === $items) {
            $items = new \EmptyIterator();
        }

        if (!\is_iterable($items)) {
            throw new EtlException('Could not extract data.');
        }

        try {
            foreach ($items as $key => $item) {
                try {
                    $this->shouldSkip = false;
                    $this->eventDispatcher->dispatch(new ItemEvent(EtlEvents::EXTRACT, $item, $key, $this));
                    yield $key => $item;
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Throwable $e) {
            /** @var ItemExceptionEvent $event */
            $event = $this->eventDispatcher->dispatch(new ItemExceptionEvent(EtlEvents::EXTRACT_EXCEPTION, $item ?? null, $key ?? null, $this, $e));
            if ($event->shouldThrowException()) {
                throw $e;
            }
        }
    }

    /**
     * Transform data.
     *
     * @param $item
     * @param $key
     * @return callable
     * @throws EtlException
     */
    private function transform($item, $key)
    {
        $transformed = ($this->transform)($item, $key, $this);

        if (!$transformed instanceof \Generator) {
            throw new EtlException('The transformer must return a generator.');
        }

        // Traverse generator to trigger events
        try {
            $transformed = \iterator_to_array($transformed);
            $this->eventDispatcher->dispatch(new ItemEvent(EtlEvents::TRANSFORM, $item, $key, $this));
        } catch (\Exception $e) {
            /** @var ItemExceptionEvent $event */
            $event = $this->eventDispatcher->dispatch(new ItemExceptionEvent(EtlEvents::TRANSFORM_EXCEPTION, $item, $key, $this, $e));
            if ($event->shouldThrowException()) {
                throw $e;
            }
        }

        return function () use ($transformed) {
            yield from $transformed;
        };
    }

    /**
     * Init the loader on the 1st item.
     */
    private function initLoader(): void
    {
        if (null === $this->init) {
            return;
        }

        ($this->init)();
    }

    /**
     * Load data.
     *
     * @param iterable $data
     */
    private function load(iterable $data, $item, $key, bool $flush, int &$flushCounter, int &$totalCounter): void
    {
        try {
            ($this->load)($data, $key, $this);
            $this->eventDispatcher->dispatch(new ItemEvent(EtlEvents::LOAD, $item, $key, $this));
        } catch (\Throwable $e) {
            /** @var ItemExceptionEvent $event */
            $event = $this->eventDispatcher->dispatch(new ItemExceptionEvent(EtlEvents::LOAD_EXCEPTION, $item, $key, $this, $e));
            if ($event->shouldThrowException()) {
                throw $e;
            }
            $flushCounter--;
            $totalCounter--;
        }

        if (true === $flush) {
            $this->flush($flushCounter, true);
        }
    }

    /**
     * Flush elements.
     */
    private function flush(int &$flushCounter, bool $partial): void
    {
        if (null === $this->flush) {
            return;
        }

        ($this->flush)($partial);
        $this->eventDispatcher->dispatch(new FlushEvent($this, $flushCounter, $partial));
        $flushCounter = 0;
    }

    /**
     * Restore loader's initial state.
     */
    private function rollback(int &$flushCounter): void
    {
        if (null === $this->rollback) {
            return;
        }

        ($this->rollback)();
        $this->eventDispatcher->dispatch(new RollbackEvent($this, $flushCounter));
        $flushCounter = 0;
    }

    /**
     * Process the end of the ETL.
     *
     * @param int $flushCounter
     * @param int $totalCounter
     */
    private function end(int $flushCounter, int $totalCounter): void
    {
        if ($this->shouldRollback) {
            $this->rollback($flushCounter);
            $totalCounter = max(0, $totalCounter - $flushCounter);
        } else {
            $this->flush($flushCounter, false);
        }
        $this->eventDispatcher->dispatch(new EndProcessEvent($this, $totalCounter));
        $this->reset();
    }

    /**
     * @return callable
     */
    private static function defaultTransformer(): callable
    {
        return function ($item, $key): \Generator {
            yield $key => $item;
        };
    }
}
