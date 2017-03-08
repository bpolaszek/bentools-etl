<?php

namespace BenTools\ETL\Tests\Iterator;

use BenTools\ETL\Iterator\JsonIterator;
use BenTools\ETL\Tests\TestSuite;
use PHPUnit\Framework\TestCase;

class JsonIteratorTest extends TestCase
{

    private static $jsonString;
    private static $expectedArray;
    private static $expectedObject;

    public static function setUpBeforeClass()
    {
        static::$jsonString = file_get_contents(TestSuite::getDataFile('dictators.json'));
        static::$expectedArray = [
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
        ];
        static::$expectedObject = [
            'usa'    =>
                (object) [
                    'country' => 'USA',
                    'name'    => 'Donald Trump',
                ],
            'russia' =>
                (object) [
                    'country' => 'Russia',
                    'name'    => 'Vladimir Poutine',
                ],
        ];
    }

    public function testIteratorWithArrayIterator()
    {
        $json     = new \ArrayIterator(json_decode(static::$jsonString, true));
        $iterator = new JsonIterator($json);
        $this->assertEquals(static::$expectedArray, iterator_to_array($iterator));
    }

    public function testIteratorWithJsonString()
    {
        $json     = static::$jsonString;
        $iterator = new JsonIterator($json);
        $this->assertEquals(static::$expectedArray, iterator_to_array($iterator));
    }

    public function testIteratorWithJsonArray()
    {
        $json     = json_decode(static::$jsonString, true);
        $iterator = new JsonIterator($json);
        $this->assertEquals(static::$expectedArray, iterator_to_array($iterator));
    }

    public function testIteratorWithJsonObject()
    {
        $json     = json_decode(static::$jsonString, false);
        $iterator = new JsonIterator($json);
        $this->assertEquals(static::$expectedObject, iterator_to_array($iterator));
    }
}
