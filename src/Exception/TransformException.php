<?php

declare(strict_types=1);

namespace BenTools\ETL\Exception;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\TransformExceptionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class TransformException extends EtlException
{
    public static function emit(EventDispatcherInterface $bus, Throwable $exception, EtlState $state): void
    {
        if (!$exception instanceof self) {
            $exception = new self('Error during transformation.', previous: $exception);
        }

        $exception = $bus->dispatch(new TransformExceptionEvent($state->getLastVersion(), $exception))->exception;

        if ($exception) {
            throw $exception;
        }
    }
}
