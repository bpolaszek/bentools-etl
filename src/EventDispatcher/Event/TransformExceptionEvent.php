<?php

declare(strict_types=1);

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\StoppableEventTrait;
use BenTools\ETL\Exception\TransformException;
use Psr\EventDispatcher\StoppableEventInterface;

final class TransformExceptionEvent extends Event implements StoppableEventInterface
{
    use StoppableEventTrait;

    public function __construct(
        public readonly EtlState $state,
        public ?TransformException $exception,
    ) {
    }

    public function removeException(): void
    {
        $this->exception = null;
    }
}
