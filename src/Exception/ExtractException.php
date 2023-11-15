<?php

declare(strict_types=1);

namespace BenTools\ETL\Exception;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\ExtractExceptionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class ExtractException extends EtlException
{
    public static function emit(EventDispatcherInterface $bus, Throwable $exception, EtlState $state): never
    {
        if (!$exception instanceof self) {
            $exception = new self('Error during extraction.', previous: $exception);
        }

        throw $bus->dispatch(new ExtractExceptionEvent($state, $exception))->exception;
    }
}
