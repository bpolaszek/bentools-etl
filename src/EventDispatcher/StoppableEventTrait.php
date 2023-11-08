<?php

declare(strict_types=1);

namespace BenTools\ETL\EventDispatcher;

trait StoppableEventTrait
{
    private bool $propagationStopped = false;

    final public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    final public function isPropagationStopped(): bool
    {
        return true === $this->propagationStopped;
    }
}
