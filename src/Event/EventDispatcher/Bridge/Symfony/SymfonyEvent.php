<?php

namespace BenTools\ETL\Event\EventDispatcher\Bridge\Symfony;

use BenTools\ETL\Event\EventDispatcher\Bridge\WrappedEventTrait;
use Symfony\Component\EventDispatcher\Event;

class SymfonyEvent extends Event
{
    use WrappedEventTrait;

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
}
