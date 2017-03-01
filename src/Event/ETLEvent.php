<?php

namespace BenTools\ETL\Event;

use BenTools\ETL\Event\EventDispatcher\EventInterface;

class ETLEvent implements EventInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $running = true;

    /**
     * ContextElementEvent constructor.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool
    {
        return !$this->running;
    }

    /**
     * @inheritDoc
     */
    public function stopPropagation(): void
    {
        $this->running = false;
    }
}
