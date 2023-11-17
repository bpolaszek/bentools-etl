<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EventDispatcher\Event\BeforeLoadEvent;
use BenTools\ETL\EventDispatcher\Event\EndEvent;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\ExtractExceptionEvent;
use BenTools\ETL\EventDispatcher\Event\FlushEvent;
use BenTools\ETL\EventDispatcher\Event\FlushExceptionEvent;
use BenTools\ETL\EventDispatcher\Event\InitEvent;
use BenTools\ETL\EventDispatcher\Event\LoadEvent;
use BenTools\ETL\EventDispatcher\Event\LoadExceptionEvent;
use BenTools\ETL\EventDispatcher\Event\StartEvent;
use BenTools\ETL\EventDispatcher\Event\TransformEvent;
use BenTools\ETL\EventDispatcher\Event\TransformExceptionEvent;
use BenTools\ETL\EventDispatcher\PrioritizedListenerProvider;

/**
 * @internal
 *
 * @template EtlExecutor
 */
trait EtlEventListenersTrait
{
    private PrioritizedListenerProvider $listenerProvider;

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
     * @param callable(BeforeLoadEvent): void $callback
     */
    public function onBeforeLoad(callable $callback, int $priority = 0): self
    {
        return $this->listenTo(BeforeLoadEvent::class, $callback, $priority);
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
        $clone = $this->cloneWith();
        $clone->listenerProvider->listenTo($eventClass, $callback, $priority);

        return $clone;
    }
}
