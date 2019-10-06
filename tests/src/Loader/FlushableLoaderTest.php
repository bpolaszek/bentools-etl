<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Etl;
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\EventDispatcher\Event\FlushEvent;
use BenTools\ETL\EventDispatcher\Event\ItemEvent;
use BenTools\ETL\Loader\LoaderInterface;
use PHPUnit\Framework\TestCase;

final class FlushableLoaderTest extends TestCase
{

    /**
     * @var LoaderInterface
     */
    private $loader;

    public function testItFlushesAtTheEnd()
    {
        $numCalls = 0;
        $expected = [
            ['foo', 'bar', 'baz'],
        ];

        $etl = EtlBuilder::init()
            ->loadInto($this->loader)
            ->onFlush(
                function (FlushEvent $event) use (&$numCalls, &$expected) {
                    $this->assertEquals($expected[$numCalls], $this->loader->getItems());
                    $numCalls++;
                }
            )
            ->createEtl();

        $etl->process(['foo', 'bar', 'baz']);
        $this->assertEquals(1, $numCalls);
    }

    public function testItFlushesEveryTime()
    {
        $numCalls = 0;
        $expected = [
            ['foo'],
            ['foo', 'bar'],
            ['foo', 'bar', 'baz'],
            ['foo', 'bar', 'baz'],
        ];

        $etl = EtlBuilder::init()
            ->loadInto($this->loader)
            ->flushEvery(1)
            ->onFlush(
                function (FlushEvent $event) use (&$numCalls, &$expected) {
                    $this->assertEquals($expected[$numCalls], $this->loader->getItems());
                    $numCalls++;
                }
            )
            ->createEtl();

        $etl->process(['foo', 'bar', 'baz']);
        $this->assertEquals(4, $numCalls);
    }

    public function testItFlushesEvery2Items()
    {
        $numCalls = 0;
        $expected = [
            ['foo', 'bar'],
            ['foo', 'bar', 'baz', 'bat'],
            ['foo', 'bar', 'baz', 'bat', 'batman'],
        ];

        $etl = EtlBuilder::init()
            ->loadInto($this->loader)
            ->flushEvery(2)
            ->onFlush(
                function (FlushEvent $event) use (&$numCalls, &$expected) {
                    $this->assertEquals($expected[$numCalls], $this->loader->getItems());
                    $numCalls++;
                }
            )
            ->createEtl();

        $etl->process(['foo', 'bar', 'baz', 'bat', 'batman']);
        $this->assertEquals(3, $numCalls);
    }

    public function testEarlyFlushCanBeTriggered()
    {
        $numCalls = 0;
        $expected = [
            ['foo'],
            ['foo', 'bar'],
            ['foo', 'bar', 'baz', 'bat'],
            ['foo', 'bar', 'baz', 'bat', 'batman'],
        ];

        $etl = EtlBuilder::init()
            ->loadInto($this->loader)
            ->flushEvery(2)
            ->onLoad(
                function (ItemEvent $event) {
                    if (0 === $event->getKey()) {
                        $event->getEtl()->triggerFlush();
                    }
                }
            )
            ->onFlush(
                function (FlushEvent $event) use (&$numCalls, &$expected) {
                    $this->assertEquals($expected[$numCalls], $this->loader->getItems());
                    $numCalls++;
                }
            )
            ->createEtl();

        $etl->process(['foo', 'bar', 'baz', 'bat', 'batman']);
        $this->assertEquals(4, $numCalls);
    }

    public function setUp()
    {
        $this->loader = new class implements LoaderInterface
        {
            private $tmpStorage;
            private $storage;
            public function init(): void
            {
                $this->tmpStorage = [];
                $this->storage = [];
            }

            public function load(\Generator $items, $key, Etl $etl): void
            {
                foreach ($items as $item) {
                    $this->tmpStorage[] = $item;
                }
            }

            public function commit(bool $partial): void
            {
                while (null !== ($item = \array_shift($this->tmpStorage))) {
                    $this->storage[] = $item;
                }
            }

            public function rollback(): void
            {
            }

            public function getItems()
            {
                return $this->storage;
            }

        };
    }

}