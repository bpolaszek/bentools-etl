<?php

namespace BenTools\ETL\Tests;

use BenTools\ETL\Etl;
use BenTools\ETL\EtlBuilder;
use BenTools\ETL\EventDispatcher\Event\EndProcessEvent;
use BenTools\ETL\EventDispatcher\Event\ItemExceptionEvent;
use BenTools\ETL\Loader\ArrayLoader;
use BenTools\ETL\Loader\NullLoader;
use PHPUnit\Framework\TestCase;

class EtlExceptionsTest extends TestCase
{

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Something wrong happened.
     */
    public function uncaught_exception_remains_uncaught()
    {

        $data = function () {
            yield 'foo';
            yield new \RuntimeException('Something wrong happened.');
            yield 'bar';
        };

        $etl = EtlBuilder::init()
            ->loadInto(new NullLoader())
            ->transformWith(
                function ($item) {
                    if ($item instanceof \RuntimeException) {
                        throw $item;
                    }
                    yield $item;
                }
            )
            ->createEtl()
        ;

        $etl->process($data());
    }

    /**
     * @test
     */
    public function exception_can_be_processed_on_extract()
    {

        $data = [
            'foo',
            new \RuntimeException('Something wrong happened.'),
            'bar',
        ];

        $extractor = function (iterable $items): iterable {

            foreach ($items as $item) {
                if ($item instanceof \RuntimeException) {
                    throw $item;
                }
                yield $item;
            }
        };

        $etl = EtlBuilder::init()
            ->loadInto($loader = new ArrayLoader($preserveKeys = false))
            ->extractFrom($extractor)
            ->onExtractException(
                function (ItemExceptionEvent $event) {
                    $event->ignoreException();
                }
            )
            ->onEnd(
                function (EndProcessEvent $event) use (&$counter) {
                    $counter = $event->getCounter();
                }
            )
            ->createEtl();

        $etl->process($data);
        $this->assertEquals(['foo'], $loader->getArray());
        $this->assertEquals(1, $counter);
    }

    /**
     * @test
     */
    public function exception_can_be_processed_on_transform()
    {

        $data = function () {
            yield 'foo';
            yield 'bar';
            yield 'baz';
        };
        $counter = null;
        $etl = EtlBuilder::init()
            ->loadInto($loader = new ArrayLoader($preserveKeys = false))
            ->transformWith(
                function ($item) {
                    if ('bar' === $item) {
                        throw new \RuntimeException('I don\'t like bar.');
                    }
                    yield $item;
                }
            )
            ->onTransformException(
                function (ItemExceptionEvent $event) {
                    $event->ignoreException();
                }
            )
            ->onEnd(
                function (EndProcessEvent $event) use (&$counter) {
                    $counter = $event->getCounter();
                }
            )
            ->createEtl()
        ;

        $etl->process($data());

        $this->assertEquals(['foo', 'baz'], $loader->getArray());
        $this->assertEquals(2, $counter);
    }

    /**
     * @test
     */
    public function exception_can_be_processed_on_load()
    {

        $data = function () {
            yield 'foo';
            yield 'bar';
            yield 'baz';
        };
        $counter = null;
        $array = [];
        $etl = EtlBuilder::init()
            ->loadInto(
                function (\Generator $items) use (&$array) {
                    foreach ($items as $item) {
                        if ('bar' === $item) {
                            throw new \RuntimeException('I don\'t like bar.');
                        }
                    }
                    $array[] = $item;
                }
            )
            ->onLoadException(
                function (ItemExceptionEvent $event) {
                    $event->ignoreException();
                }
            )
            ->onEnd(
                function (EndProcessEvent $event) use (&$counter) {
                    $counter = $event->getCounter();
                }
            )
            ->createEtl()
        ;

        $etl->process($data());

        $this->assertEquals(['foo', 'baz'], $array);
        $this->assertEquals(2, $counter);
    }
}
