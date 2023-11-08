<?php

declare(strict_types=1);

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\EtlState;

final class EndEvent extends Event
{
    public function __construct(
        public readonly EtlState $state,
    ) {
    }
}
