<?php

namespace BenTools\ETL\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ListenerProviderInterface $listenerProvider,
    ) {
    }

    /**
     * @template T of object
     *
     * @param T $event
     *
     * @return T
     */
    public function dispatch(object $event): object
    {
        $listeners = $this->listenerProvider->getListenersForEvent($event);
        $isStoppable = $event instanceof StoppableEventInterface;

        foreach ($listeners as $callback) {
            if ($isStoppable && $event->isPropagationStopped()) {
                break;
            }

            $callback($event);
        }

        return $event;
    }
}
