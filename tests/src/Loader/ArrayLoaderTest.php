<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Runner\ETLRunner;
use PHPUnit\Framework\TestCase;

use BenTools\ETL\Loader\ArrayLoader;

class ArrayLoaderTest extends TestCase
{

    public function testLoader()
    {
        $items = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];
        $extractor = new KeyValueExtractor();
        $loader = new ArrayLoader();
        $run = new ETLRunner();
        $run($items, $extractor, null, $loader);
        $this->assertEquals($loader->getArray(), $items);
    }
}
