<?php

declare(strict_types=1);

namespace Bentools\ETL;

use Bentools\ETL\EventDispatcher\Event\EndEvent;
use Bentools\ETL\EventDispatcher\Event\ExtractEvent;
use Bentools\ETL\EventDispatcher\Event\FlushEvent;
use Bentools\ETL\EventDispatcher\Event\InitEvent;
use Bentools\ETL\EventDispatcher\Event\LoadEvent;
use Bentools\ETL\EventDispatcher\Event\StartEvent;
use Bentools\ETL\EventDispatcher\Event\TransformEvent;
use Bentools\ETL\EventDispatcher\EventDispatcher;
use Bentools\ETL\EventDispatcher\PrioritizedListenerProvider;
use Bentools\ETL\Exception\SkipRequest;
use Bentools\ETL\Exception\StopRequest;
use Bentools\ETL\Extractor\ExtractorInterface;
use Bentools\ETL\Extractor\IterableExtractor;
use Bentools\ETL\Internal\ClonableTrait;
use Bentools\ETL\Internal\EtlBuilderTrait;
use Bentools\ETL\Internal\EtlExceptionsTrait;
use Bentools\ETL\Internal\Ref;
use Bentools\ETL\Loader\InMemoryLoader;
use Bentools\ETL\Loader\LoaderInterface;
use Bentools\ETL\Transformer\NullTransformer;
use Bentools\ETL\Transformer\TransformerInterface;
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

            foreach ($this->extract($stateHolder) as $extractedItem) {
                try {
                    $transformedItems = $this->transform($extractedItem, $state);
                    $this->load($transformedItems, $stateHolder);
                } catch (SkipRequest) {
                }
            }
        } catch (StopRequest) {
        }

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
            $transformed = $this->transformer->transform($item, $state);
            $items = $transformed instanceof Generator ? [...$transformed] : [$transformed];

            return $this->dispatch(new TransformEvent($state, $items))->items;
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
