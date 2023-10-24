<?php

declare(strict_types=1);

namespace Bentools\ETL\Recipe;

use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EventDispatcher\Event\EndEvent;
use Bentools\ETL\EventDispatcher\Event\Event;
use Bentools\ETL\EventDispatcher\Event\ExtractEvent;
use Bentools\ETL\EventDispatcher\Event\ExtractExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\FlushEvent;
use Bentools\ETL\EventDispatcher\Event\FlushExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\InitEvent;
use Bentools\ETL\EventDispatcher\Event\LoadEvent;
use Bentools\ETL\EventDispatcher\Event\LoadExceptionEvent;
use Bentools\ETL\EventDispatcher\Event\StartEvent;
use Bentools\ETL\EventDispatcher\Event\TransformEvent;
use Bentools\ETL\EventDispatcher\Event\TransformExceptionEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Stringable;

/**
 * @codeCoverageIgnore
 */
final class LoggerRecipe extends Recipe
{
    /**
     * @param array<class-string, string> $logLevels
     * @param array<class-string, int>    $priorities
     */
    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly array $logLevels = [
            StartEvent::class => LogLevel::INFO,
            FlushEvent::class => LogLevel::INFO,
            EndEvent::class => LogLevel::INFO,
            ExtractExceptionEvent::class => LogLevel::ERROR,
            TransformExceptionEvent::class => LogLevel::ERROR,
            LoadExceptionEvent::class => LogLevel::ERROR,
            FlushExceptionEvent::class => LogLevel::ERROR,
        ],
        private readonly string $defaultLogLevel = LogLevel::DEBUG,
        private readonly array $priorities = [],
        private readonly int $defaultPriority = -1,
    ) {
    }

    public function fork(EtlExecutor $executor): EtlExecutor
    {
        return $executor
            ->onInit(fn (InitEvent $event) => $this->log($event, 'Initializing ETL...', ['state' => $event->state]),
                $this->priorities[InitEvent::class] ?? $this->defaultPriority)
            ->onStart(fn (StartEvent $event) => $this->log($event, 'Starting ETL...', ['state' => $event->state]),
                $this->priorities[StartEvent::class] ?? $this->defaultPriority)
            ->onExtract(
                fn (ExtractEvent $event) => $this->log(
                    $event,
                    'Extracting item #{key}',
                    [
                        'key' => $event->state->currentItemKey,
                        'state' => $event->state,
                        'item' => $event->item,
                    ],
                ),
                $this->priorities[ExtractEvent::class] ?? $this->defaultPriority,
            )
            ->onExtractException(
                fn (ExtractExceptionEvent $event) => $this->log(
                    $event,
                    'Extract exception on key #{key}: {msg}',
                    [
                        'msg' => $event->exception->getMessage(),
                        'key' => $event->state->currentItemKey,
                        'state' => $event->state,
                    ],
                ),
                $this->priorities[ExtractExceptionEvent::class] ?? $this->defaultPriority,
            )
            ->onTransform(
                fn (TransformEvent $event) => $this->log(
                    $event,
                    'Transformed item #{key}',
                    [
                        'key' => $event->state->currentItemKey,
                        'state' => $event->state,
                        'items' => $event->items,
                    ],
                ),
                $this->priorities[TransformEvent::class] ?? $this->defaultPriority,
            )
            ->onTransformException(
                fn (TransformExceptionEvent $event) => $this->log(
                    $event,
                    'Transform exception on key #{key}: {msg}',
                    [
                        'msg' => $event->exception->getMessage(),
                        'key' => $event->state->currentItemKey,
                        'state' => $event->state,
                    ],
                ),
                $this->priorities[TransformExceptionEvent::class] ?? $this->defaultPriority,
            )
            ->onLoad(
                fn (LoadEvent $event) => $this->log(
                    $event,
                    'Loaded item #{key}',
                    [
                        'key' => $event->state->currentItemKey,
                        'state' => $event->state,
                        'item' => $event->item,
                    ],
                ),
                $this->priorities[LoadEvent::class] ?? $this->defaultPriority,
            )
            ->onLoadException(
                fn (LoadExceptionEvent $event) => $this->log(
                    $event,
                    'Load exception on key #{key}: {msg}',
                    [
                        'msg' => $event->exception->getMessage(),
                        'key' => $event->state->currentItemKey,
                        'state' => $event->state,
                    ],
                ),
                $this->priorities[LoadExceptionEvent::class] ?? $this->defaultPriority,
            )
            ->onFlush(
                fn (FlushEvent $event) => $this->log(
                    $event,
                    $event->partial ? 'Flushing items (partial)...' : 'Flushing items...',
                    [
                        'key' => $event->state->currentItemKey,
                        'state' => $event->state,
                    ],
                ),
                $this->priorities[FlushEvent::class] ?? $this->defaultPriority,
            )
            ->onFlushException(
                fn (FlushExceptionEvent $event) => $this->log(
                    $event,
                    'Flush exception: {msg}',
                    [
                        'msg' => $event->exception->getMessage(),
                        'state' => $event->state,
                    ],
                ),
                $this->priorities[FlushExceptionEvent::class] ?? $this->defaultPriority,
            )
            ->onEnd(
                fn (EndEvent $event) => $this->log(
                    $event,
                    'ETL complete. {nb} items were loaded in {duration}s.',
                    [
                        'nb' => $event->state->nbLoadedItems,
                        'duration' => $event->state->getDuration(),
                        'state' => $event->state,
                    ],
                ),
                $this->priorities[EndEvent::class] ?? $this->defaultPriority,
            );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function log(Event $event, string|Stringable $message, array $context = []): void
    {
        $level = $this->logLevels[$event::class] ?? $this->defaultLogLevel;

        $this->logger->log($level, $message, $context);
    }
}
