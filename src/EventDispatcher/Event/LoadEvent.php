<?php

declare(strict_types=1);

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\StoppableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;

final class LoadEvent extends Event implements StoppableEventInterface
{
    use StoppableEventTrait;

    public function __construct(
        public readonly EtlState $state,
        public readonly mixed $item,
    ) {
    }
}
