<?php

declare(strict_types=1);

namespace Bentools\ETL\EventDispatcher\Event;

use Bentools\ETL\EtlState;
use Bentools\ETL\EventDispatcher\StoppableEventTrait;
use Bentools\ETL\Exception\TransformException;
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
