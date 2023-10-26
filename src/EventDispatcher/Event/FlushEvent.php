<?php

declare(strict_types=1);

namespace Bentools\ETL\EventDispatcher\Event;

use Bentools\ETL\EtlState;
use Bentools\ETL\EventDispatcher\StoppableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;

final class FlushEvent extends Event implements StoppableEventInterface
{
    use StoppableEventTrait;

    public function __construct(
        public readonly EtlState $state,
        public readonly bool $partial,
        public mixed $output,
    ) {
    }
}
