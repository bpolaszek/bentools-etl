<?php

namespace BenTools\ETL\Event\EventDispatcher\Bridge\Symfony;

use BenTools\ETL\Event\EventDispatcher\EventInterface;
use Symfony\Component\EventDispatcher\Event;

class SymfonyEvent extends Event
{

    /**
     * @var EventInterface
     */
    private $wrappedEvent;

    /**
     * SymfonyEvent constructor.
     *
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
    public function isPropagationStopped()
    {
        return $this->wrappedEvent->isPropagationStopped();
    }

    /**
     * @inheritDoc
     */
    public function stopPropagation()
    {
        $this->wrappedEvent->stopPropagation();
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
