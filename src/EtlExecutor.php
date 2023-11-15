<?php

declare(strict_types=1);

namespace BenTools\ETL;

use BenTools\ETL\EventDispatcher\Event\EndEvent;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\FlushEvent;
use BenTools\ETL\EventDispatcher\Event\InitEvent;
use BenTools\ETL\EventDispatcher\Event\LoadEvent;
use BenTools\ETL\EventDispatcher\Event\StartEvent;
use BenTools\ETL\EventDispatcher\Event\TransformEvent;
use BenTools\ETL\EventDispatcher\EventDispatcher;
use BenTools\ETL\EventDispatcher\PrioritizedListenerProvider;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Exception\FlushException;
use BenTools\ETL\Exception\LoadException;
use BenTools\ETL\Exception\SkipRequest;
use BenTools\ETL\Exception\StopRequest;
use BenTools\ETL\Exception\TransformException;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Extractor\IterableExtractor;
use BenTools\ETL\Internal\ClonableTrait;
use BenTools\ETL\Internal\ConditionalLoaderTrait;
use BenTools\ETL\Internal\EtlBuilderTrait;
use BenTools\ETL\Internal\TransformResult;
use BenTools\ETL\Loader\InMemoryLoader;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Transformer\NullTransformer;
use BenTools\ETL\Transformer\TransformerInterface;
use Generator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function count;
use function gc_collect_cycles;
use function is_countable;

final class EtlExecutor
{
    use ClonableTrait;

    /**
     * @use EtlBuilderTrait<self>
     */
    use EtlBuilderTrait;

    use ConditionalLoaderTrait;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        public readonly ExtractorInterface $extractor = new IterableExtractor(),
        public readonly TransformerInterface $transformer = new NullTransformer(),
        public readonly LoaderInterface $loader = new InMemoryLoader(),
        public readonly EtlConfiguration $options = new EtlConfiguration(),
    ) {
        $this->listenerProvider = new PrioritizedListenerProvider();
        $this->eventDispatcher = new EventDispatcher($this->listenerProvider);
    }

    /**
     * @param mixed        $source      - Optional arbitrary argument which will be passed to the Extractor
     * @param mixed        $destination - Optional arbitrary argument which will be passed to the Loader
     * @param array<mixed> $context     - Optional arbitrary data which will be passed to the ETLState object
     */
    public function process(mixed $source = null, mixed $destination = null, array $context = []): EtlState
    {
        $state = new EtlState(options: $this->options, source: $source, destination: $destination, context: $context);

        try {
            $this->dispatch(new InitEvent($state));

            foreach ($this->extract($state) as $e => $extractedItem) {
                $state = $state->getLastVersion();
                if (0 !== $e) {
                    $this->consumeNextTick($state);
                }
                try {
                    $transformedItems = $this->transform($extractedItem, $state);
                    $this->load($transformedItems, $state);
                } catch (SkipRequest) {
                }
            }
        } catch (StopRequest) {
        }

        return $this->terminate($state->getLastVersion());
    }

    /**
     * @internal
     */
    public function consumeNextTick(EtlState $state): void
    {
        foreach ($state->nextTickCallbacks as $callback) {
            ($callback)($state);
            $state->nextTickCallbacks->detach($callback);
        }
    }

    private function extract(EtlState $state): Generator
    {
        $state = $state->getLastVersion();
        try {
            $items = $this->extractor->extract($state);
            if (is_countable($items)) {
                $state = $state->update($state->withNbTotalItems(count($items)));
            }
            $this->dispatch(new StartEvent($state));
            foreach ($items as $key => $value) {
                try {
                    $state = $state->update($state->getLastVersion()->withUpdatedItemKey($key));
                    $event = $this->dispatch(new ExtractEvent($state, $value));
                    yield $event->item;
                } catch (SkipRequest) {
                }
            }
        } catch (StopRequest) {
            return;
        } catch (Throwable $exception) {
            ExtractException::emit($this->eventDispatcher, $exception, $state->getLastVersion());
        }
    }

    /**
     * @return list<mixed>
     *
     * @internal
     */
    public function transform(mixed $item, EtlState $state): array
    {
        try {
            $transformResult = TransformResult::create($this->transformer->transform($item, $state));

            $event = $this->dispatch(new TransformEvent($state, $transformResult));
            $transformResult = TransformResult::create($event->transformResult);

            return [...$transformResult];
        } catch (SkipRequest|StopRequest $e) {
            throw $e;
        } catch (Throwable $e) {
            TransformException::emit($this->eventDispatcher, $e, $state);
        }

        return [];
    }

    /**
     * @param list<mixed> $items
     *
     * @internal
     */
    private function load(array $items, EtlState $state): void
    {
        try {
            foreach ($items as $item) {
                if (!self::shouldLoad($this->loader, $item, $state)) {
                    continue;
                }
                $this->loader->load($item, $state);
                $state = $state->update($state->getLastVersion()->withIncrementedNbLoadedItems());
                $this->dispatch(new LoadEvent($state, $item));
            }
        } catch (SkipRequest|StopRequest $e) {
            throw $e;
        } catch (Throwable $e) {
            LoadException::emit($this->eventDispatcher, $e, $state->getLastVersion());
        }

        $this->flush($state->getLastVersion(), true);
    }

    /**
     * @internal
     */
    public function flush(EtlState $state, bool $isPartial): mixed
    {
        if ($isPartial && !$state->shouldFlush()) {
            return null;
        }

        if (0 === $state->nbLoadedItems) {
            return null;
        }

        $output = null;
        $state->flush();
        try {
            $output = $this->loader->flush($isPartial, $state);
        } catch (Throwable $e) {
            FlushException::emit($this->eventDispatcher, $e, $state);
        }
        $this->dispatch(new FlushEvent($state, $isPartial, $output));
        $state->update($state->withClearedFlush());

        return $output;
    }

    /**
     * @internal
     */
    public function terminate(EtlState $state): EtlState
    {
        $this->consumeNextTick($state);
        $output = $this->flush($state->getLastVersion(), false);

        $state = $state->getLastVersion();
        if (!$state->nbTotalItems) {
            $state = $state->update($state->withNbTotalItems($state->nbLoadedItems));
        }

        $state = $state->update($state->withOutput($output));
        $this->dispatch(new EndEvent($state->getLastVersion()));

        gc_collect_cycles();

        return $state;
    }

    /**
     * @param T $event
     *
     * @return T
     *
     * @internal
     *
     * @template T of object
     */
    public function dispatch(object $event): object
    {
        $this->eventDispatcher->dispatch($event);

        return $event;
    }
}
