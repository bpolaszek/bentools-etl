<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\BeforeLoadEvent;
use BenTools\ETL\EventDispatcher\Event\Event;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\TransformEvent;

/**
 * @internal
 *
 * @template EtlExecutor
 */
trait DispatchEventsTrait
{
    /**
     * @internal
     *
     * @template T of object
     *
     * @param T $event
     *
     * @return T
     */
    public function dispatch(object $event): object
    {
        $this->eventDispatcher->dispatch($event);

        return $event;
    }

    /**
     * @template E of Event
     *
     * @param class-string<E> $eventClass
     *
     * @return E|null
     */
    private function emit(string $eventClass, EtlState $state, mixed ...$args): ?Event
    {
        if (!$this->listenerProvider->hasListeners($eventClass)) {
            return null;
        }

        return $this->dispatch(new $eventClass($state, ...$args));
    }

    private function emitExtractEvent(EtlState $state, mixed $item): mixed
    {
        $event = $this->emit(ExtractEvent::class, $state, $item);

        return $event?->item ?? $item;
    }

    private function emitTransformEvent(EtlState $state, TransformResult $transformResult): TransformResult
    {
        $event = $this->emit(TransformEvent::class, $state, $transformResult);

        return TransformResult::create($event?->transformResult ?? $transformResult);
    }

    private function emitBeforeLoadEvent(EtlState $state, mixed $item): mixed
    {
        $event = $this->emit(BeforeLoadEvent::class, $state, $item);

        return $event?->item ?? $item;
    }
}
