<?php

declare(strict_types=1);

namespace Bentools\ETL\Internal;

use Bentools\ETL\EtlState;
use Bentools\ETL\EventDispatcher\Event\ExtractExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\FlushExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\LoadExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\TransformExceptionEvent;
use Bentools\ETL\Exception\ExtractException;
use Bentools\ETL\Exception\FlushException;
use Bentools\ETL\Exception\LoadException;
use Bentools\ETL\Exception\TransformException;
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
