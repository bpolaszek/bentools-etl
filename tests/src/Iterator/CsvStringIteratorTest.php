<?php

namespace BenTools\ETL\Tests\Iterator;

use BenTools\ETL\Iterator\CsvStringIterator;
use BenTools\ETL\Tests\TestSuite;
use PHPUnit\Framework\TestCase;

class CsvStringIteratorTest extends TestCase
{
    /**
     * @test
     */
    public function it_iterates()
    {
        $text     = file_get_contents(TestSuite::getDataFile('dictators.csv'));
        $iterator = CsvStringIterator::createFromText($text);
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
