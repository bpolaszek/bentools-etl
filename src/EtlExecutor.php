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
use BenTools\ETL\Extractor\ExtractorProcessorInterface;
use BenTools\ETL\Extractor\IterableExtractor;
use BenTools\ETL\Extractor\IterableExtractorProcessor;
use BenTools\ETL\Internal\ClonableTrait;
use BenTools\ETL\Internal\ConditionalLoaderTrait;
use BenTools\ETL\Internal\EtlBuilderTrait;
use BenTools\ETL\Internal\TransformResult;
use BenTools\ETL\Loader\InMemoryLoader;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Transformer\NullTransformer;
use BenTools\ETL\Transformer\TransformerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function count;
use function gc_collect_cycles;
use function get_debug_type;
use function sprintf;

final class EtlExecutor implements EventDispatcherInterface
{
    use ClonableTrait;

    /**
     * @use EtlBuilderTrait<self>
     */
    use EtlBuilderTrait;

    use ConditionalLoaderTrait;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        public readonly ExtractorInterface $extractor = new IterableExtractor(),
        public readonly TransformerInterface $transformer = new NullTransformer(),
        public readonly LoaderInterface $loader = new InMemoryLoader(),
        public readonly EtlConfiguration $options = new EtlConfiguration(),
        public readonly ExtractorProcessorInterface $processor = new IterableExtractorProcessor(),
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
            $items = $this->extractor->extract($state);

            $state = $state->getLastVersion();
            if (is_countable($items)) {
                $state = $state->update($state->withNbTotalItems(count($items)));
            }
            $this->dispatch(new StartEvent($state));

            if (!$this->processor->supports($items)) {
                throw new ExtractException(sprintf('Current processor %s cannot process data of type: %s.', $this->processor::class, get_debug_type($items)));
            }

            $this->processor->process($this, $state, $items);
        } catch (StopRequest) {
        }

        return $this->terminate($state->getLastVersion());
    }

    public function processItem(mixed $item, mixed $key, EtlState $state): void
    {
        $state = $state->update($state->getLastVersion()->withUpdatedItemKey($key));
        if ($state->currentItemIndex > 0) {
            $this->consumeNextTick($state);
        }
        $event = $this->dispatch(new ExtractEvent($state, $item));
        $item = $event->item;
        $itemsToLoad = $this->transform($item, $state);
        $this->load($itemsToLoad, $state);
    }

    /**
     * @internal
     */
    private function consumeNextTick(EtlState $state): void
    {
        foreach ($state->nextTickCallbacks as $callback) {
            ($callback)($state);
            $state->nextTickCallbacks->detach($callback);
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
            LoadException::emit($this->eventDispatcher, $e, $state);
        }

        $this->flush($state->getLastVersion(), true);
    }

    /**
     * @internal
     */
    private function flush(EtlState $state, bool $isPartial): mixed
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
    private function terminate(EtlState $state): EtlState
    {
        $this->consumeNextTick($state);
        $output = $this->flush($state->getLastVersion(), false);

        $state = $state->getLastVersion();
        if (!$state->nbTotalItems) {
            $state = $state->update($state->withNbTotalItems($state->nbLoadedItems));
        }

        $state = $state->update($state->withOutput($output));
        $this->dispatch(new EndEvent($state));

        gc_collect_cycles();

        return $state;
    }

    /**
     * @internal
     *
     * @param T $event
     *
     * @return T
     *
     * @template T of object
     */
    public function dispatch(object $event): object
    {
        $this->eventDispatcher->dispatch($event);

        return $event;
    }
}
