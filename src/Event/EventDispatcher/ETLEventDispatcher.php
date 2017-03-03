<?php

namespace BenTools\ETL\Event\EventDispatcher;

class ETLEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var array
     */
    private $listeners = [];

    /**
     * @param string $eventName
     * @param callable $listener
     */
    public function addListener(string $eventName, callable $listener): void
    {
        if (!array_key_exists($eventName, $this->listeners)) {
            $this->listeners[$eventName] = [];
        }
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * @inheritDoc
     */
    public function trigger(EventInterface $event): void
    {
        if (!empty($this->listeners[$event->getName()])) {
            foreach ($this->listeners[$event->getName()] AS $listen) {
                if (!$event->isPropagationStopped()) {
                    $listen($event);
                }
            }
        }
    }

}