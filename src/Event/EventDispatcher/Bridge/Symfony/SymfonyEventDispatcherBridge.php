<?php

namespace BenTools\ETL\Event\EventDispatcher\Bridge\Symfony;

use BenTools\ETL\Event\EventDispatcher\Bridge\WrappedDispatcherTrait;
use BenTools\ETL\Event\EventDispatcher\EventDispatcherInterface;
use BenTools\ETL\Event\EventDispatcher\EventInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

class SymfonyEventDispatcherBridge implements EventDispatcherInterface
{

    use WrappedDispatcherTrait;

    /**
     * SymfonyEventDispatcherBridge constructor.
     *
     * @param SymfonyEventDispatcherInterface $eventDispatcher
     */
    public function __construct(SymfonyEventDispatcherInterface $eventDispatcher = null)
    {
        $this->wrappedDispatcher = $eventDispatcher ?? new EventDispatcher();
    }

    /**
     * @inheritdoc
     */
    public function trigger(EventInterface $event): void
    {
        $symfonyEvent = new SymfonyEvent($event);
        $this->wrappedDispatcher->dispatch($event->getName(), $symfonyEvent);
    }
}
