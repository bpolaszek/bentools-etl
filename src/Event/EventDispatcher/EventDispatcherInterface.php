<?php

namespace BenTools\ETL\Event\EventDispatcher;

interface EventDispatcherInterface
{
    /**
     * Dispatch the event.
     * @param EventInterface $event
     */
    public function trigger(EventInterface $event): void;
}
