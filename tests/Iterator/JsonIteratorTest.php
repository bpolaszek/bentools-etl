<?php

namespace BenTools\ETL\Tests\Iterator;

use PHPUnit\Framework\TestCase;
use BenTools\ETL\Iterator\JsonIterator;

class JsonIteratorTest extends TestCase
{

    public function testIterator()
    {
        $json     = file_get_contents(__DIR__ . '/../data/dictators.json');
        $iterator = new JsonIterator($json);
        $this->assertEquals([
            'usa'    =>
                [
                    'country' => 'USA',
                    'name'    => 'Donald Trump',
                ],
            'russia' =>
                [
                    'country' => 'Russia',
                    'name'    => 'Vladimir Poutine',
                ],
        ], iterator_to_array($iterator));
    }

}
