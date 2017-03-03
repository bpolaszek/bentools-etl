<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Runner\ETLRunner;
use PHPUnit\Framework\TestCase;

use BenTools\ETL\Loader\DebugLoader;

class DebugLoaderTest extends TestCase
{

    public function testLoader()
    {
        $items     = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];
        $extractor = new KeyValueExtractor();
        $debug     = null;
        $loader    = new DebugLoader([], function ($data) use (&$debug) {
            $debug = [
                'myData' => $data,
            ];
        });
        $run       = new ETLRunner();
        $run($items, $extractor, null, $loader);
        $this->assertEquals([
            'myData' => $items,
        ], $debug);
    }

}
