<?php

namespace BenTools\ETL\Tests\Iterator;

use BenTools\ETL\Iterator\TextLinesIterator;
use PHPUnit\Framework\TestCase;

class TextLinesIteratorTest extends TestCase
{

    private $text;

    public function setUp()
    {
        $this->text = <<<EOF
foo


bar
EOF;
    }

    public function testIterator()
    {

        $iterator = new TextLinesIterator($this->text);
        $this->assertEquals([
            'foo',
            'bar'
        ], iterator_to_array($iterator));

    }

    public function testIteratorWithoutSkippingEmptyLines()
    {

        $iterator = new TextLinesIterator($this->text, false);
        $this->assertEquals([
            'foo',
            '',
            '',
            'bar'
        ], iterator_to_array($iterator));

    }

}
