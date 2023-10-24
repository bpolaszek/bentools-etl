<?php

declare(strict_types=1);

namespace Bentools\ETL\Internal;

use Bentools\ETL\EventDispatcher\Event\EndEvent;
use Bentools\ETL\EventDispatcher\Event\ExtractEvent;
use Bentools\ETL\EventDispatcher\Event\ExtractExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\FlushEvent;
use Bentools\ETL\EventDispatcher\Event\FlushExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\InitEvent;
use Bentools\ETL\EventDispatcher\Event\LoadEvent;
use Bentools\ETL\EventDispatcher\Event\LoadExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\StartEvent;
use Bentools\ETL\EventDispatcher\Event\TransformEvent;
use Bentools\ETL\EventDispatcher\Event\TransformExceptionEvent;
use Bentools\ETL\EventDispatcher\PrioritizedListenerProvider;

/**
 * @internal
 *
 * @template T
 */
trait EtlEventListenersTrait
{
    private readonly PrioritizedListenerProvider $listenerProvider;

    /**
     * @param callable(InitEvent): void $callback
     */
    public function onInit(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(InitEvent::class, $callback, $priority);
    }

    /**
     * @param callable(StartEvent): void $callback
     */
    public function onStart(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(StartEvent::class, $callback, $priority);
    }

    /**
     * @param callable(ExtractEvent): void $callback
     */
    public function onExtract(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(ExtractEvent::class, $callback, $priority);
    }

    /**
     * @param callable(ExtractExceptionEvent): void $callback
     */
    public function onExtractException(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(ExtractExceptionEvent::class, $callback, $priority);
    }

    /**
     * @param callable(TransformEvent): void $callback
     */
    public function onTransform(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(TransformEvent::class, $callback, $priority);
    }

    /**
     * @param callable(TransformExceptionEvent): void $callback
     */
    public function onTransformException(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(TransformExceptionEvent::class, $callback, $priority);
    }

    /**
     * @param callable(LoadEvent): void $callback
     */
    public function onLoad(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(LoadEvent::class, $callback, $priority);
    }

    /**
     * @param callable(LoadExceptionEvent): void $callback
     */
    public function onLoadException(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(LoadExceptionEvent::class, $callback, $priority);
    }

    /**
     * @param callable(FlushEvent): void $callback
     */
    public function onFlush(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(FlushEvent::class, $callback, $priority);
    }

    /**
     * @param callable(FlushExceptionEvent): void $callback
     */
    public function onFlushException(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(FlushExceptionEvent::class, $callback, $priority);
    }

    /**
     * @param callable(EndEvent): void $callback
     */
    public function onEnd(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(EndEvent::class, $callback, $priority);
    }

    private function listenTo(string $eventClass, callable $callback, int $priority = 0): self
    {
        $clone = $this->clone();
        $clone->listenerProvider->listenTo($eventClass, $callback, $priority);

        return $clone;
    }
}
