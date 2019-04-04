<?php

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\Etl;
use BenTools\ETL\EventDispatcher\EtlEvents;

final class EndProcessEvent extends EtlEvent
{
    /**
     * @var int
     */
    private $counter;

    /**
     * EndProcessEvent constructor.
     *
     * @param Etl $etl
     */
    public function __construct(Etl $etl, int $counter)
    {
        parent::__construct($etl);
        $this->counter = $counter;
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return EtlEvents::END;
    }
}
