<?php

namespace BenTools\ETL\Event\EventDispatcher;

use Throwable;

interface EventInterface
{

    /**
     * Returns the event name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns wether or not the dispatcher should stop the event's propagation.
     *
     * @return bool
     */
    public function isPropagationStopped(): bool;

    /**
     * Stops the event propagation.
     */
    public function stopPropagation(): void;

    /**
     * @return bool
     */
    public function hasException(): bool;

    /**
     * @return null|Throwable
     */
    public function getException(): ?Throwable;
}
