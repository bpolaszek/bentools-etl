<?php

namespace BenTools\ETL\Tests\Iterator;

use BenTools\ETL\Iterator\CsvFileIterator;
use BenTools\ETL\Tests\TestSuite;
use PHPUnit\Framework\TestCase;

class CsvFileIteratorTest extends TestCase
{

    public function testIterator()
    {
        $iterator = CsvFileIterator::createFromFilename(TestSuite::getDataFile('dictators.csv'));
        $this->assertCount(3, $iterator);
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
