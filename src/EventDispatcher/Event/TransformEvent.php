<?php

declare(strict_types=1);

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\StoppableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;

final class TransformEvent extends Event implements StoppableEventInterface
{
    use StoppableEventTrait;

    /**
     * @param list<mixed> $items
     */
    public function __construct(
        public readonly EtlState $state,
        public array $items,
    ) {
    }
}
