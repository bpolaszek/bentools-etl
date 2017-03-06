<?php

namespace BenTools\ETL\Event\EventDispatcher;

class NullEventDispatcher implements EventDispatcherInterface
{
    /**
     * @inheritDoc
     */
    public function addListener(string $eventName, callable $callback): void
    {
        // nope.
    }

    /**
     * @inheritdoc
     */
    public function trigger(EventInterface $event): void
    {
        // nope.
    }
}
