<?php

namespace BenTools\ETL\Event;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\EventDispatcher\EventInterface;

class ContextElementEvent implements EventInterface {

    /**
     * @var string
     */
    private $name;

    /**
     * @var ContextElementInterface
     */
    private $element;

    /**
     * @var bool
     */
    private $running = true;

    /**
     * ContextElementEvent constructor.
     */
    public function __construct(string $name, ContextElementInterface $element) {
        $this->name = $name;
        $this->element = $element;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return ContextElementInterface
     */
    public function getElement(): ContextElementInterface {
        return $this->element;
    }

    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool {
        return !$this->running;
    }

    /**
     * @inheritDoc
     */
    public function stopPropagation(): void {
        $this->running = false;
    }
}