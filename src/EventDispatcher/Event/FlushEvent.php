<?php

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\Etl;
use BenTools\ETL\EventDispatcher\EtlEvents;

final class FlushEvent extends EtlEvent
{
    /**
     * @var int
     */
    private $counter;
    /**
     * @var bool
     */
    private $partial;

    /**
     * EndProcessEvent constructor.
     *
     * @param Etl $etl
     */
    public function __construct(Etl $etl, int $counter, bool $partial)
    {
        parent::__construct($etl);
        $this->counter = $counter;
        $this->partial = $partial;
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * @return bool
     */
    public function isPartial(): bool
    {
        return $this->partial;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return EtlEvents::FLUSH;
    }
}
