<?php

namespace BenTools\ETL\Event\EventDispatcher\Bridge\Symfony;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\ETLEventInterface;
use BenTools\ETL\Event\EventDispatcher\EventInterface;
use Symfony\Component\EventDispatcher\Event;

class SymfonyEvent extends Event implements ETLEventInterface {

    /**
     * @var EventInterface
     */
    private $wrappedEvent;

    /**
     * SymfonyEvent constructor.
     * @param EventInterface $wrappedEvent
     */
    public function __construct(ETLEventInterface $wrappedEvent) {
        $this->wrappedEvent = $wrappedEvent;
    }

    /**
     * @inheritDoc
     */
    public function getElement(): ContextElementInterface {
        return $this->wrappedEvent->getElement();
    }

    /**
     * @return EventInterface
     */
    public function getWrappedEvent() {
        return $this->wrappedEvent;
    }

    /**
     * @inheritDoc
     */
    public function isPropagationStopped() {
        return $this->wrappedEvent->isPropagationStopped();
    }

    /**
     * @inheritDoc
     */
    public function stopPropagation() {
        $this->wrappedEvent->stopPropagation();
    }
}