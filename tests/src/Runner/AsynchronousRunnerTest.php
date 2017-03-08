<?php

namespace BenTools\ETL\Tests\Runner;

use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Loader\ArrayLoader;
use BenTools\ETL\Transformer\CallbackTransformer;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;

use BenTools\ETL\Runner\AsynchronousRunner;

class AsynchronousRunnerTest extends TestCase
{

    public function testRunner()
    {
        $run = new AsynchronousRunner();
        $items = ['foo', 'bar'];
        $extract = new KeyValueExtractor();
        $transform = new CallbackTransformer('strtoupper');
        $load = new ArrayLoader();

        $promise = $run($items, $extract, $transform, $load);
        $this->assertInstanceOf(PromiseInterface::class, $promise);
        $this->assertEquals([], $load->getArray());

        $promise->wait();

        $this->assertEquals(PromiseInterface::FULFILLED, $promise->getState());
        $this->assertEquals(['FOO', 'BAR'], $load->getArray());
    }
}
