<?php

namespace BenTools\ETL\Tests\Iterator;

use PHPUnit\Framework\TestCase;
use BenTools\ETL\Iterator\CsvFileIterator;

class CsvFileIteratorTest extends TestCase
{

    public function testIterator()
    {
        $file     = new \SplFileObject(__DIR__ . '/../data/dictators.csv');
        $iterator = new CsvFileIterator($file);
        $this->assertEquals([
            [
                'country',
                'name',
            ],
            [
                'USA',
                'Donald Trump',
            ],
            [
                'Russia',
                'Vladimir Poutine',
            ],
        ], array_values(iterator_to_array($iterator)));
    }

}
