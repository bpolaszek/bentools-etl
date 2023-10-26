<?php

declare(strict_types=1);

namespace Bentools\ETL\EventDispatcher\Event;

use Bentools\ETL\EtlState;
use Bentools\ETL\EventDispatcher\StoppableEventTrait;
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
