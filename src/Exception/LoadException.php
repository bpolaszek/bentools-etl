<?php

declare(strict_types=1);

namespace BenTools\ETL\Exception;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\LoadExceptionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class LoadException extends EtlException
{
    public static function emit(EventDispatcherInterface $bus, Throwable $exception, EtlState $state): void
    {
        if (!$exception instanceof self) {
            $exception = new self('Error during loading.', previous: $exception);
        }

        $exception = $bus->dispatch(new LoadExceptionEvent($state->getLastVersion(), $exception))->exception;

        if ($exception) {
            throw $exception;
        }
    }
}
