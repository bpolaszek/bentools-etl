<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\StartEvent;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Exception\SkipRequest;
use BenTools\ETL\Exception\StopRequest;
use Generator;
use Throwable;

use function BenTools\ETL\unref;
use function count;
use function is_iterable;

final readonly class IterableProcessor implements ExtractorProcessorInterface
{
    public function supports(mixed $extracted): bool
    {
        return is_iterable($extracted);
    }

    /**
     * @param Ref<EtlState>   $stateHolder
     * @param iterable<mixed> $items
     */
    public function process(EtlExecutor $executor, Ref $stateHolder, mixed $items): EtlState
    {
        $state = unref($stateHolder);
        if (is_countable($items)) {
            $state = $state->withNbTotalItems(count($items));
            $stateHolder->update($state);
        }
        $executor->dispatch(new StartEvent($state));

        foreach ($this->extract($executor, $stateHolder, $items) as $e => $extractedItem) {
            $state = unref($stateHolder);
            if (0 !== $e) {
                $executor->consumeNextTick($state);
            }
            try {
                $executor->processItem($extractedItem, $stateHolder);
            } catch (SkipRequest) {
            }
        }

        return unref($stateHolder);
    }

    /**
     * @param Ref<EtlState>   $stateHolder
     * @param iterable<mixed> $items
     */
    public function extract(EtlExecutor $executor, Ref $stateHolder, iterable $items): Generator
    {
        try {
            foreach ($items as $key => $value) {
                try {
                    $state = unref($stateHolder)->withUpdatedItemKey($key);
                    $stateHolder->update($state);
                    $event = $executor->dispatch(new ExtractEvent($state, $value));
                    yield $event->item;
                } catch (SkipRequest) {
                }
            }
        } catch (StopRequest) {
            return;
        } catch (Throwable $exception) {
            ExtractException::emit($executor, $exception, unref($stateHolder));
        }
    }
}
