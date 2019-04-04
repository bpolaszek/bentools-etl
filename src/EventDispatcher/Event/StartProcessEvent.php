<?php

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\EventDispatcher\EtlEvents;

final class StartProcessEvent extends EtlEvent
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return EtlEvents::START;
    }
}
