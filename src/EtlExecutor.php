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
use BenTools\ETL\Exception\SkipRequest;
use BenTools\ETL\Exception\StopRequest;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Extractor\IterableExtractor;
use BenTools\ETL\Internal\ClonableTrait;
use BenTools\ETL\Internal\ConditionalLoaderTrait;
use BenTools\ETL\Internal\EtlBuilderTrait;
use BenTools\ETL\Internal\EtlExceptionsTrait;
use BenTools\ETL\Internal\Ref;
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

    /**
     * @use EtlExceptionsTrait<self>
     */
    use EtlExceptionsTrait;

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
        $stateHolder = ref($state);

        try {
            $this->dispatch(new InitEvent($state));

            foreach ($this->extract($stateHolder) as $e => $extractedItem) {
                $state = unref($stateHolder);
                if (0 !== $e) {
                    $this->consumeNextTick($state);
                }
                try {
                    $transformedItems = $this->transform($extractedItem, $state);
                    $this->load($transformedItems, $stateHolder);
                } catch (SkipRequest) {
                }
            }
        } catch (StopRequest) {
        }

        $this->consumeNextTick($state);
        $output = $this->flush($stateHolder, false);

        $state = unref($stateHolder);
        if (!$state->nbTotalItems) {
            $state = $state->withNbTotalItems($state->nbLoadedItems);
            $stateHolder->update($state);
        }

        $state = $state->withOutput($output);
        $stateHolder->update($state);
        $this->dispatch(new EndEvent($state));

        gc_collect_cycles();

        return $state;
    }

    private function consumeNextTick(EtlState $state): void
    {
        if (null === $state->nextTickCallback) {
            return;
        }

        ($state->nextTickCallback)($state);
    }

    /**
     * @param Ref<EtlState> $stateHolder
     */
    private function extract(Ref $stateHolder): Generator
    {
        $state = unref($stateHolder);
        try {
            $items = $this->extractor->extract($state);
            if (is_countable($items)) {
                $state = $state->withNbTotalItems(count($items));
                $stateHolder->update($state);
            }
            $this->dispatch(new StartEvent($state));
            foreach ($items as $key => $value) {
                try {
                    $state = unref($stateHolder)->withUpdatedItemKey($key);
                    $stateHolder->update($state);
                    $event = $this->dispatch(new ExtractEvent($state, $value));
                    yield $event->item;
                } catch (SkipRequest) {
                }
            }
        } catch (StopRequest) {
            return;
        } catch (Throwable $exception) {
            $this->throwExtractException($exception, unref($stateHolder));
        }
    }

    /**
     * @return list<mixed>
     */
    private function transform(mixed $item, EtlState $state): array
    {
        try {
            $transformResult = TransformResult::create($this->transformer->transform($item, $state));

            $event = $this->dispatch(new TransformEvent($state, $transformResult));
            $transformResult = TransformResult::create($event->transformResult);

            return [...$transformResult];
        } catch (SkipRequest|StopRequest $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->throwTransformException($e, $state);
        }

        return [];
    }

    /**
     * @param list<mixed>   $items
     * @param Ref<EtlState> $stateHolder
     */
    private function load(array $items, Ref $stateHolder): void
    {
        $state = unref($stateHolder);
        try {
            foreach ($items as $item) {
                if (!self::shouldLoad($this->loader, $item, $state)) {
                    continue;
                }
                $this->loader->load($item, $state);
                $state = $state->withIncrementedNbLoadedItems();
                $stateHolder->update($state);
                $this->dispatch(new LoadEvent($state, $item));
            }
        } catch (SkipRequest|StopRequest $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->throwLoadException($e, unref($stateHolder));
        }

        $this->flush($stateHolder, true);
    }

    /**
     * @param Ref<EtlState> $stateHolder
     */
    private function flush(Ref $stateHolder, bool $isPartial): mixed
    {
        $state = unref($stateHolder);
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
            $this->throwFlushException($e, $state);
        }
        $this->dispatch(new FlushEvent($state, $isPartial, $output));
        $stateHolder->update($state->withClearedFlush());

        return $output;
    }

    /**
     * @template T of object
     *
     * @param T $event
     *
     * @return T
     */
    private function dispatch(object $event): object
    {
        $this->eventDispatcher->dispatch($event);

        return $event;
    }
}
