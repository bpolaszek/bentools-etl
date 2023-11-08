<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\ExtractExceptionEvent;
use BenTools\ETL\EventDispatcher\Event\FlushExceptionEvent;
use BenTools\ETL\EventDispatcher\Event\LoadExceptionEvent;
use BenTools\ETL\EventDispatcher\Event\TransformExceptionEvent;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Exception\FlushException;
use BenTools\ETL\Exception\LoadException;
use BenTools\ETL\Exception\TransformException;
use Throwable;

/**
 * @internal
 *
 * @template T
 */
trait EtlExceptionsTrait
{
    private function throwExtractException(Throwable $exception, EtlState $state): never
    {
        if (!$exception instanceof ExtractException) {
            $exception = new ExtractException('Error during extraction.', previous: $exception);
        }

        throw $this->dispatch(new ExtractExceptionEvent($state, $exception))->exception;
    }

    private function throwTransformException(Throwable $exception, EtlState $state): void
    {
        if (!$exception instanceof TransformException) {
            $exception = new TransformException('Error during transformation.', previous: $exception);
        }

        $exception = $this->dispatch(new TransformExceptionEvent($state, $exception))->exception;

        if ($exception) {
            throw $exception;
        }
    }

    private function throwLoadException(Throwable $exception, EtlState $state): void
    {
        if (!$exception instanceof LoadException) {
            $exception = new LoadException('Error during loading.', previous: $exception);
        }

        $exception = $this->dispatch(new LoadExceptionEvent($state, $exception))->exception;

        if ($exception) {
            throw $exception;
        }
    }

    private function throwFlushException(Throwable $exception, EtlState $state): void
    {
        if (!$exception instanceof FlushException) {
            $exception = new FlushException('Error during flush.', previous: $exception);
        }

        $exception = $this->dispatch(new FlushExceptionEvent($state, $exception))->exception;

        if ($exception) {
            throw $exception;
        }
    }
}
