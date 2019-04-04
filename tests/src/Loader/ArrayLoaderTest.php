<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Loader\ArrayLoader;
use PHPUnit\Framework\TestCase;
use function BenTools\ETL\Tests\create_generator;
use function BenTools\ETL\Tests\dummy_etl;

class ArrayLoaderTest extends TestCase
{

    public function testLoader()
    {
        $items = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];

        $loader = new ArrayLoader();
        foreach ($items as $key => $value) {
            $loader->load(create_generator([$key => $value]), $key, dummy_etl());
        }
        $this->assertEquals($items, $loader->getArray());
    }
}
