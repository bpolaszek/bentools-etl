<?php

declare(strict_types=1);

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\EtlState;

abstract class Event
{
    public readonly EtlState $state; // @phpstan-ignore-line
}
