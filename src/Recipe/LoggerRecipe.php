<?php

namespace BenTools\ETL\Recipe;

use BenTools\ETL\EtlBuilder;
use BenTools\ETL\EventDispatcher\EtlEvents;
use BenTools\ETL\EventDispatcher\Event\EndProcessEvent;
use BenTools\ETL\EventDispatcher\Event\EtlEvent;
use BenTools\ETL\EventDispatcher\Event\FlushEvent;
use BenTools\ETL\EventDispatcher\Event\ItemEvent;
use BenTools\ETL\EventDispatcher\Event\RollbackEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LoggerRecipe extends Recipe
{
    private const DEFAULT_LOG_LEVELS = [
        EtlEvents::START       => LogLevel::INFO,
        EtlEvents::EXTRACT     => LogLevel::INFO,
        EtlEvents::TRANSFORM   => LogLevel::INFO,
        EtlEvents::LOADER_INIT => LogLevel::INFO,
        EtlEvents::LOAD        => LogLevel::INFO,
        EtlEvents::FLUSH       => LogLevel::INFO,
        EtlEvents::SKIP        => LogLevel::INFO,
        EtlEvents::STOP        => LogLevel::INFO,
        EtlEvents::ROLLBACK    => LogLevel::INFO,
        EtlEvents::END         => LogLevel::INFO,
    ];

    private const DEFAULT_EVENT_PRIORITIES = [
        EtlEvents::START       => 128,
        EtlEvents::EXTRACT     => 128,
        EtlEvents::TRANSFORM   => 128,
        EtlEvents::LOADER_INIT => 128,
        EtlEvents::LOAD        => 128,
        EtlEvents::FLUSH       => 128,
        EtlEvents::SKIP        => 128,
        EtlEvents::STOP        => 128,
        EtlEvents::ROLLBACK    => 128,
        EtlEvents::END         => 128,
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $logLevels;

    /**
     * @var array
     */
    private $eventPriorities;

    /**
     * LoggerRecipe constructor.
     */
    public function __construct(LoggerInterface $logger, array $logLevels = [], array $eventPriorities = [])
    {
        $this->logger = $logger;
        $this->logLevels = \array_replace(self::DEFAULT_LOG_LEVELS, $logLevels);
        $this->eventPriorities = \array_replace(self::DEFAULT_EVENT_PRIORITIES, $eventPriorities);
    }

    /**
     * @inheritDoc
     */
    public function updateBuilder(EtlBuilder $builder): EtlBuilder
    {
        return $builder
            ->onStart(
                function (EtlEvent $event) {
                    $this->logger->log($this->getLogLevel($event), 'Starting ETL...');
                },
                $this->getPriority(EtlEvents::START)
            )
            ->onExtract(
                function (ItemEvent $event) {
                    $this->logger->log($this->getLogLevel($event), sprintf('Extracted %s.', $event->getKey()));
                },
                $this->getPriority(EtlEvents::EXTRACT)
            )
            ->onTransform(
                function (ItemEvent $event) {
                    $this->logger->log($this->getLogLevel($event), sprintf('Transformed %s.', $event->getKey()));
                },
                $this->getPriority(EtlEvents::TRANSFORM)
            )
            ->onLoaderInit(
                function (ItemEvent $event) {
                    $this->logger->log($this->getLogLevel($event), 'Initializing loader...');
                },
                $this->getPriority(EtlEvents::LOAD)
            )
            ->onLoad(
                function (ItemEvent $event) {
                    $this->logger->log($this->getLogLevel($event), sprintf('Loaded %s.', $event->getKey()));
                },
                $this->getPriority(EtlEvents::LOAD)
            )
            ->onSkip(
                function (ItemEvent $event) {
                    $this->logger->log($this->getLogLevel($event), sprintf('Skipping item %s.', $event->getKey()));
                },
                $this->getPriority(EtlEvents::SKIP)
            )
            ->onStop(
                function (ItemEvent $event) {
                    $this->logger->log($this->getLogLevel($event), sprintf('Stopping on item %s.', $event->getKey()));
                },
                $this->getPriority(EtlEvents::STOP)
            )
            ->onFlush(
                function (FlushEvent $event) {
                    $this->logger->log($this->getLogLevel($event), sprintf('Flushed %d items.', $event->getCounter()));
                },
                $this->getPriority(EtlEvents::FLUSH)
            )
            ->onRollback(
                function (RollbackEvent $event) {
                    $this->logger->log($this->getLogLevel($event), sprintf('Rollback %d items.', $event->getCounter()));
                },
                $this->getPriority(EtlEvents::ROLLBACK)
            )
            ->onEnd(
                function (EndProcessEvent $event) {
                    $this->logger->log($this->getLogLevel($event), sprintf('ETL completed on %d items.', $event->getCounter()));
                },
                $this->getPriority(EtlEvents::END)
            );
    }

    /**
     * @param EtlEvent $event
     * @return string
     */
    private function getLogLevel(EtlEvent $event): string
    {
        return $this->logLevels[$event->getName()] ?? LogLevel::INFO;
    }

    /**
     * @param EtlEvent $event
     * @return int
     */
    private function getPriority(string $eventName): int
    {
        return $this->eventPriorities[$eventName] ?? 128;
    }
}
