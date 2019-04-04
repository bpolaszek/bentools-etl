<?php

namespace BenTools\ETL\EventDispatcher;

final class EventListener
{
    /**
     * @var string
     */
    private $eventName;

    /**
     * @var callable
     */
    private $listener;

    /**
     * @var int
     */
    private $priority;

    /**
     * EventListener constructor.
     */
    public function __construct(string $eventName, callable $listener, int $priority = 0)
    {
        $this->eventName = $eventName;
        $this->listener = $listener;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    /**
     * @return callable
     */
    public function getCallable(): callable
    {
        return $this->listener;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}
