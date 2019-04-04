<?php

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\Etl;
use Psr\EventDispatcher\StoppableEventInterface;

abstract class EtlEvent implements StoppableEventInterface
{
    /**
     * @var Etl
     */
    protected $etl;

    /**
     * @var bool
     */
    private $propagationStopped = false;

    /**
     * EtlEvent constructor.
     *
     * @param Etl $etl
     */
    public function __construct(Etl $etl)
    {
        $this->etl = $etl;
    }

    /**
     * @return Etl
     */
    public function getEtl(): Etl
    {
        return $this->etl;
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Stop event propagation.
     */
    final public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * @inheritDoc
     */
    final public function isPropagationStopped(): bool
    {
        return true === $this->propagationStopped;
    }
}
