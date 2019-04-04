<?php

namespace BenTools\ETL\Tests\Recipe;

use BenTools\ETL\EtlBuilder;
use BenTools\ETL\EventDispatcher\Event\ItemEvent;
use BenTools\ETL\Loader\NullLoader;
use BenTools\ETL\Recipe\LoggerRecipe;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggerRecipeTest extends TestCase
{
    /**
     * @test
     */
    public function it_logs_everything()
    {
        $logger = $this->createLogger();
        $builder = EtlBuilder::init()
            ->loadInto(new NullLoader())
            ->useRecipe(new LoggerRecipe($logger));
        $etl = $builder->createEtl();
        $etl->process([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $expected = [
            'Starting ETL...',
            'Extracted foo.',
            'Transformed foo.',
            'Loaded foo.',
            'Extracted bar.',
            'Transformed bar.',
            'Loaded bar.',
            'Flushed 2 items.',
            'ETL completed on 2 items.',
        ];

        $this->assertEquals($expected, $logger->stack);
    }

    /**
     * @test
     */
    public function it_also_logs_skipping_items()
    {
        $logger = $this->createLogger();
        $builder = EtlBuilder::init()
            ->loadInto(new NullLoader())
            ->onExtract(
                function (ItemEvent $event) {
                    if ('foo' === $event->getKey()) {
                        $event->getEtl()->skipCurrentItem();
                    }
                })
            ->useRecipe(new LoggerRecipe($logger))
        ;
        $etl = $builder->createEtl();
        $etl->process([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $expected = [
            'Starting ETL...',
            'Extracted foo.',
            'Skipping item foo.',
            'Extracted bar.',
            'Transformed bar.',
            'Loaded bar.',
            'Flushed 1 items.',
            'ETL completed on 1 items.',
        ];

        $this->assertEquals($expected, $logger->stack);
    }

    /**
     * @test
     */
    public function it_also_logs_stop_event()
    {
        $logger = $this->createLogger();
        $builder = EtlBuilder::init()
            ->loadInto(new NullLoader())
            ->onExtract(
                function (ItemEvent $event) {
                    if ('foo' === $event->getKey()) {
                        $event->getEtl()->stopProcessing();
                    }
                })
            ->useRecipe(new LoggerRecipe($logger))
        ;
        $etl = $builder->createEtl();
        $etl->process([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $expected = [
            'Starting ETL...',
            'Extracted foo.',
            'Stopping on item foo.',
            'Flushed 0 items.',
            'ETL completed on 0 items.',
        ];

        $this->assertEquals($expected, $logger->stack);
    }

    /**
     * @test
     */
    public function it_also_logs_rollback_event()
    {
        $logger = $this->createLogger();
        $builder = EtlBuilder::init()
            ->loadInto(new NullLoader())
            ->onExtract(
                function (ItemEvent $event) {
                    if ('foo' === $event->getKey()) {
                        $event->getEtl()->stopProcessing(true);
                    }
                })
            ->useRecipe(new LoggerRecipe($logger))
        ;
        $etl = $builder->createEtl();
        $etl->process([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $expected = [
            'Starting ETL...',
            'Extracted foo.',
            'Stopping on item foo.',
            'Rollback 0 items.',
            'ETL completed on 0 items.',
        ];

        $this->assertEquals($expected, $logger->stack);
    }

    private function createLogger(): LoggerInterface
    {
        return new class implements LoggerInterface
        {
            public $stack = [];
            public function emergency($message, array $context = []) {}
            public function alert($message, array $context = []) {}
            public function critical($message, array $context = []) {}
            public function error($message, array $context = []) {}
            public function warning($message, array $context = []) {}
            public function notice($message, array $context = []) {}
            public function info($message, array $context = []) {}
            public function debug($message, array $context = []) {}
            public function log($level, $message, array $context = [])
            {
                $this->stack[] = $message;
            }
        };
    }

}
