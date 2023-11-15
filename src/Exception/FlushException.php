<?php

declare(strict_types=1);

namespace BenTools\ETL\Exception;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\FlushExceptionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class FlushException extends EtlException
{
    public static function emit(EventDispatcherInterface $bus, Throwable $exception, EtlState $state): void
    {
        if (!$exception instanceof self) {
            $exception = new self('Error during flush.', previous: $exception);
        }

        $exception = $bus->dispatch(new FlushExceptionEvent($state, $exception))->exception;

        if ($exception) {
            throw $exception;
        }
    }
}
