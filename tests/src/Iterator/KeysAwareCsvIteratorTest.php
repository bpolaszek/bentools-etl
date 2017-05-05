<?php

namespace BenTools\ETL\Tests\Iterator;

use BenTools\ETL\Iterator\CsvFileIterator;
use BenTools\ETL\Iterator\CsvStringIterator;
use BenTools\ETL\Iterator\KeysAwareCsvIterator;
use BenTools\ETL\Tests\TestSuite;
use PHPUnit\Framework\TestCase;

class KeysAwareCsvIteratorTest extends TestCase
{
    public function testIteratorWithKeysDiscovery()
    {
        $file     = new \SplFileObject(TestSuite::getDataFile('dictators.csv'));
        $iterator = new KeysAwareCsvIterator(new CsvFileIterator($file));
        $result   = iterator_to_array($iterator);
        $this->assertEquals([
            [
                'country' => 'USA',
                'name' => 'Donald Trump',
            ],
            [
                'country' => 'Russia',
                'name' => 'Vladimir Poutine',
            ],
        ], $result);
    }

    public function testIteratorWithSpecifiedKeys()
    {
        $file     = new \SplFileObject(TestSuite::getDataFile('dictators.csv'));
        $iterator = new KeysAwareCsvIterator(new CsvFileIterator($file), ['Country', 'Name']);
        $result   = iterator_to_array($iterator);
        $this->assertEquals([
            [
                'Country' => 'USA',
                'Name' => 'Donald Trump',
            ],
            [
                'Country' => 'Russia',
                'Name' => 'Vladimir Poutine',
            ],
        ], $result);
    }

    public function testIteratorWithoutSkippingFirstRow()
    {
        $file     = new \SplFileObject(TestSuite::getDataFile('dictators.csv'));
        $iterator = new KeysAwareCsvIterator(new CsvFileIterator($file), ['Country', 'Name'], false);
        $result   = iterator_to_array($iterator);
        $this->assertEquals([
            [
                'Country' => 'country',
                'Name' => 'name',
            ],
            [
                'Country' => 'USA',
                'Name' => 'Donald Trump',
            ],
            [
                'Country' => 'Russia',
                'Name' => 'Vladimir Poutine',
            ],
        ], $result);
    }

    public function testCreateFromText()
    {
        $text     = file_get_contents(TestSuite::getDataFile('dictators.csv'));
        $iterator = new KeysAwareCsvIterator(CsvStringIterator::createFromText($text));
        $result   = iterator_to_array($iterator);
        $this->assertEquals([
            [
                'country' => 'USA',
                'name' => 'Donald Trump',
            ],
            [
                'country' => 'Russia',
                'name' => 'Vladimir Poutine',
            ],
        ], $result);
    }
}
