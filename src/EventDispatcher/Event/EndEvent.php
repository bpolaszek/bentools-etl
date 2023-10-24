<?php

declare(strict_types=1);

namespace Bentools\ETL\EventDispatcher\Event;

use Bentools\ETL\EtlState;

final class EndEvent extends Event
{
    public function __construct(
        public readonly EtlState $state,
    ) {
    }
}
