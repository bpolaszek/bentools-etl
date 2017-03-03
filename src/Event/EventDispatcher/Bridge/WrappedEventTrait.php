<?php

namespace BenTools\ETL\Event\EventDispatcher\Bridge;

use BenTools\ETL\Event\EventDispatcher\EventInterface;

trait WrappedEventTrait
{
    /**
     * @var EventInterface
     */
    protected $wrappedEvent;

    /**
     * WrappedEventTrait constructor.
     * @param EventInterface $wrappedEvent
     */
    public function __construct(EventInterface $wrappedEvent)
    {
        $this->wrappedEvent = $wrappedEvent;
    }

    /**
     * @return EventInterface
     */
    public function getWrappedEvent()
    {
        return $this->wrappedEvent;
    }

    /**
     * @inheritDoc
     */
    public function __call($name, $arguments)
    {
        $wrappedEvent = $this->wrappedEvent;
        return $wrappedEvent->$name(...$arguments);
    }
}
