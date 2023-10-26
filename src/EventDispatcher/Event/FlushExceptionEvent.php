<?php

declare(strict_types=1);

namespace Bentools\ETL\EventDispatcher\Event;

use Bentools\ETL\EtlState;
use Bentools\ETL\EventDispatcher\StoppableEventTrait;
use Bentools\ETL\Exception\FlushException;
use Psr\EventDispatcher\StoppableEventInterface;

final class FlushExceptionEvent extends Event implements StoppableEventInterface
{
    use StoppableEventTrait;

    public function __construct(
        public readonly EtlState $state,
        public ?FlushException $exception,
    ) {
    }

    public function removeException(): void
    {
        $this->exception = null;
    }
}
