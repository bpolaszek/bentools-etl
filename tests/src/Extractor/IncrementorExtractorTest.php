<?php

namespace BenTools\ETL\Tests\Extractor;

use BenTools\ETL\Context\ContextElementInterface;
use PHPUnit\Framework\TestCase;

use BenTools\ETL\Extractor\IncrementorExtractor;

class IncrementorExtractorTest extends TestCase
{

    public function testExtractor()
    {
        $extract = new IncrementorExtractor();

        $element = $extract('foo', 'bar');
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertEquals(0, $element->getId());
        $this->assertEquals('bar', $element->getData());

        $element = $extract('bar', 'baz');
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertEquals(1, $element->getId());
        $this->assertEquals('baz', $element->getData());

    }

    public function testExtractorWithAnotherStartIndex()
    {
        $extract = new IncrementorExtractor(10);

        $element = $extract('foo', 'bar');
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertEquals(10, $element->getId());
        $this->assertEquals('bar', $element->getData());

        $element = $extract('bar', 'baz');
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertEquals(11, $element->getId());
        $this->assertEquals('baz', $element->getData());

    }

}
