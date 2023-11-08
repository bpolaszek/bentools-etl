<?php

declare(strict_types=1);

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\StoppableEventTrait;
use BenTools\ETL\Exception\LoadException;
use Psr\EventDispatcher\StoppableEventInterface;

final class LoadExceptionEvent extends Event implements StoppableEventInterface
{
    use StoppableEventTrait;

    public function __construct(
        public readonly EtlState $state,
        public ?LoadException $exception,
    ) {
    }

    public function removeException(): void
    {
        $this->exception = null;
    }
}
