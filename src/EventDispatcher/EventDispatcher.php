<?php

namespace BenTools\ETL\EventDispatcher;

use BenTools\ETL\EventDispatcher\Event\EtlEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

final class EventDispatcher implements EventDispatcherInterface, ListenerProviderInterface
{

    /**
     * @var EventListener[]
     */
    private $listeners = [];

    /**
     * EventDispatcher constructor.
     *
     * @param EventListener $listeners
     */
    public function __construct(iterable $listeners = null)
    {
        $listeners = $listeners ?? [];
        foreach ($listeners as $listener) {
            $this->addListener($listener);
        }
    }

    /**
     * @param EventListener $eventListener
     */
    private function addListener(EventListener $eventListener): void
    {
        $this->listeners[] = $eventListener;
    }


    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        if (!$event instanceof EtlEvent) {
            return [];
        }

        $listenersForEvent = \array_filter(
            $this->listeners,
            function (EventListener $eventListener) use ($event) {
                return $eventListener->getEventName() === $event->getName();
            }
        );

        \usort(
            $listenersForEvent,
            function (EventListener $a, EventListener $b) {
                return $b->getPriority() <=> $a->getPriority();
            }
        );

        return \array_map(
            function (EventListener $eventListener) {
                return $eventListener->getCallable();
            },
            $listenersForEvent
        );
    }

    /**
     * @inheritDoc
     */
    public function dispatch(object $event)
    {
        if (!$event instanceof EtlEvent) {
            return $event;
        }

        $listeners = $this->getListenersForEvent($event);

        foreach ($listeners as $listen) {
            if ($event->isPropagationStopped()) {
                break;
            }

            $listen($event);
        }

        return $event;
    }
}
