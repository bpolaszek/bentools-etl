<?php

namespace BenTools\ETL\Tests;

use BenTools\ETL\Etl;
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\EventDispatcher\EtlEvents;
use BenTools\ETL\EventDispatcher\Event\EtlEvent;
use BenTools\ETL\EventDispatcher\Event\ItemEvent;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Loader\NullLoader;
use BenTools\ETL\Transformer\CallableTransformer;
use PHPUnit\Framework\TestCase;

class EtlBuilderTest extends TestCase
{
    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Loader has not been provided.
     */
    public function it_yells_if_no_loader_is_provided()
    {
        $builder = EtlBuilder::init();
        $builder->createEtl();
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The "flushEvery" option must be null or an integer > 0.
     */
    public function it_yells_on_invalid_flush_setting()
    {
        $builder = EtlBuilder::init()->loadInto(
            function () {

            })->flushEvery(0);
        $builder->createEtl();
    }

    /**
     * @test
     */
    public function it_builds_an_etl_object()
    {
        $builder = EtlBuilder::init(
            null,
            null,
            function () {

            }
        );
        $etl = $builder->createEtl();
        $this->assertInstanceOf(Etl::class, $etl);
    }

    /**
     * @test
     */
    public function it_correctly_builds_an_etl_object()
    {
        $extractor = new class implements ExtractorInterface
        {
            public function extract($input, Etl $etl): iterable
            {
                return $input['foos'];
            }
        };

        $transformer = new CallableTransformer('strtoupper');

        $loader = new class implements LoaderInterface
        {
            public $initiated;
            public $storage;
            public $committed;
            public $rollback;

            public function reset()
            {
                $this->initiated = false;
                $this->storage = [];
                $this->committed = false;
                $this->rollback = false;
                return $this;
            }

            /**
             * @inheritDoc
             */
            public function init(): void
            {
                $this->initiated = true;
            }

            /**
             * @inheritDoc
             */
            public function load(\Generator $items, $key, Etl $etl): void
            {
                foreach ($items as $item) {
                    $this->storage[] = $item;
                }
            }

            /**
             * @inheritDoc
             */
            public function commit(bool $partial): void
            {
                $this->committed = true;
            }

            /**
             * @inheritDoc
             */
            public function rollback(): void
            {
                $this->rollback = true;
            }
        };

        $etl = EtlBuilder::init($extractor, $transformer, $loader)->createEtl();

        $data = [
            'foos' => [
                'foo',
                'bar',
            ],
        ];

        $etl->process($data);

        $this->assertTrue($loader->initiated);
        $this->assertTrue($loader->committed);
        $this->assertEquals(['FOO', 'BAR'], $loader->storage);

        $loader = $loader->reset();
        $etl = EtlBuilder::init($extractor, function ($item, $key, Etl $etl) {
            $etl->stopProcessing(true);
            yield;
        },
            $loader
        )->createEtl();
        $etl->process($data);

        $this->assertTrue($loader->rollback);

    }

    /**
     * @test
     */
    public function it_correctly_maps_events()
    {
        $data = ['foo'];
        $calledEvents = [];
        $logEvent = function (EtlEvent $event) use (&$calledEvents) {
            $calledEvents[] = $event->getName();
        };
        $builder = EtlBuilder::init()->loadInto(new NullLoader())
            ->onStart($logEvent)
            ->onExtract($logEvent)
            ->onTransform($logEvent)
            ->onLoad($logEvent)
            ->onFlush($logEvent)
            ->onSkip($logEvent)
            ->onStop($logEvent)
            ->onEnd($logEvent)
            ->onRollback($logEvent)
        ;

        $etl = $builder->createEtl();
        $etl->process($data);

        $this->assertEquals([
            EtlEvents::START,
            EtlEvents::EXTRACT,
            EtlEvents::TRANSFORM,
            EtlEvents::LOAD,
            EtlEvents::FLUSH,
            EtlEvents::END,
        ], $calledEvents);
    }

    /**
     * @test
     */
    public function it_correctly_maps_skipping_events()
    {
        $data = ['foo', 'bar'];
        $calledEvents = [];
        $logEvent = function (EtlEvent $event) use (&$calledEvents) {
            $calledEvents[] = $event->getName();
        };
        $builder = EtlBuilder::init()->loadInto(new NullLoader())
            ->onStart($logEvent)
            ->onExtract(
                function (ItemEvent $event) {
                    if ('foo' === $event->getItem()) {
                        $event->getEtl()->skipCurrentItem();
                    }
                })
            ->onExtract($logEvent)
            ->onTransform($logEvent)
            ->onLoad($logEvent)
            ->onFlush($logEvent)
            ->onSkip($logEvent)
            ->onStop($logEvent)
            ->onEnd($logEvent)
            ->onRollback($logEvent)
        ;

        $etl = $builder->createEtl();
        $etl->process($data);

        $this->assertEquals([
            EtlEvents::START,
            EtlEvents::EXTRACT,
            EtlEvents::SKIP,
            EtlEvents::EXTRACT,
            EtlEvents::TRANSFORM,
            EtlEvents::LOAD,
            EtlEvents::FLUSH,
            EtlEvents::END,
        ], $calledEvents);
    }

    /**
     * @test
     */
    public function it_correctly_maps_stop_events()
    {
        $data = ['foo', 'bar'];
        $calledEvents = [];
        $logEvent = function (EtlEvent $event) use (&$calledEvents) {
            $calledEvents[] = $event->getName();
        };
        $builder = EtlBuilder::init()->loadInto(new NullLoader())
            ->onStart($logEvent)
            ->onExtract(
                function (ItemEvent $event) {
                    if ('foo' === $event->getItem()) {
                        $event->getEtl()->stopProcessing();
                    }
                })
            ->onExtract($logEvent)
            ->onTransform($logEvent)
            ->onLoad($logEvent)
            ->onFlush($logEvent)
            ->onSkip($logEvent)
            ->onStop($logEvent)
            ->onEnd($logEvent)
            ->onRollback($logEvent)
        ;

        $etl = $builder->createEtl();
        $etl->process($data);

        $this->assertEquals([
            EtlEvents::START,
            EtlEvents::EXTRACT,
            EtlEvents::STOP,
            EtlEvents::FLUSH,
            EtlEvents::END,
        ], $calledEvents);
    }

    /**
     * @test
     */
    public function it_correctly_maps_rollback_events()
    {
        $data = ['foo', 'bar'];
        $calledEvents = [];
        $logEvent = function (EtlEvent $event) use (&$calledEvents) {
            $calledEvents[] = $event->getName();
        };
        $builder = EtlBuilder::init()->loadInto(new NullLoader())
            ->onStart($logEvent)
            ->onExtract(
                function (ItemEvent $event) {
                    if ('foo' === $event->getItem()) {
                        $event->getEtl()->stopProcessing(true);
                    }
                })
            ->onExtract($logEvent)
            ->onTransform($logEvent)
            ->onLoad($logEvent)
            ->onFlush($logEvent)
            ->onSkip($logEvent)
            ->onStop($logEvent)
            ->onEnd($logEvent)
            ->onRollback($logEvent)
        ;

        $etl = $builder->createEtl();
        $etl->process($data);

        $this->assertEquals([
            EtlEvents::START,
            EtlEvents::EXTRACT,
            EtlEvents::STOP,
            EtlEvents::ROLLBACK,
            EtlEvents::END,
        ], $calledEvents);
    }
}
