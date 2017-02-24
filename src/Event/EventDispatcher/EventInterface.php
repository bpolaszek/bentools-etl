<?php

namespace BenTools\ETL\Event\EventDispatcher;

interface EventInterface {

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Returns wether or not the dispatcher should stop the event's propagation.
     * @return bool
     */
    public function isPropagationStopped(): bool;

    /**
     * Stops the event propagation.
     */
    public function stopPropagation(): void;

}