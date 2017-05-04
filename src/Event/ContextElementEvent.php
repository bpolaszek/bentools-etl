<?php

namespace BenTools\ETL\Event;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\EventDispatcher\EventInterface;
use Throwable;

class ContextElementEvent implements EventInterface
{

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
     * @var \Throwable
     */
    private $exception;

    /**
     * ContextElementEvent constructor.
     */
    public function __construct(string $name, ContextElementInterface $element)
    {
        $this->name = $name;
        $this->element = $element;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ContextElementInterface
     */
    public function getElement(): ContextElementInterface
    {
        return $this->element;
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

    /**
     * @inheritDoc
     */
    public function hasException(): bool
    {
        return null !== $this->exception;
    }

    /**
     * @inheritDoc
     */
    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * @param Throwable $exception
     * @return $this - Provides Fluent Interface
     */
    public function setException(Throwable $exception = null)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * Removes the exception, if any.
     *
     * @return $this
     */
    public function removeException()
    {
        $this->exception = null;
        return $this;
    }
}
