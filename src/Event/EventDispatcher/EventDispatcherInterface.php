<?php

namespace BenTools\ETL\Event\EventDispatcher;

interface EventDispatcherInterface
{
    /**
     * @param $eventName
     * @param callable  $callback
     */
    public function addListener(string $eventName, callable $callback): void;

    /**
     * Dispatch the event.
     *
     * @param EventInterface $event
     */
    public function trigger(EventInterface $event): void;
}
