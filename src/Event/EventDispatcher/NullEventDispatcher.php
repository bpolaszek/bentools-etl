<?php

namespace BenTools\ETL\Event\EventDispatcher;

class NullEventDispatcher implements EventDispatcherInterface {

    /**
     * @inheritdoc
     */
    public function trigger(EventInterface $event): void {
        // nope.
    }
}