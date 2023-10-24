<?php

declare(strict_types=1);

namespace Bentools\ETL\EventDispatcher\Event;

use Bentools\ETL\EtlState;

abstract class Event
{
    public readonly EtlState $state; // @phpstan-ignore-line
}
