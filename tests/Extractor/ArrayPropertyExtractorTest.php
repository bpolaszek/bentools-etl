<?php

namespace BenTools\ETL\Tests\Extractor;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Extractor\ArrayPropertyExtractor;
use PHPUnit\Framework\TestCase;

class ArrayPropertyExtractorTest extends TestCase
{

    public function testExtractorShift()
    {
        $extract = new ArrayPropertyExtractor('bar', true);
        $element = $extract('foo', [
            'lorem' => 'ipsum',
            'bar'   => 'baz',
            'dolor' => 'sit amet',
        ]);
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertEquals('baz', $element->getId());
        $this->assertEquals([
            'lorem' => 'ipsum',
            'dolor' => 'sit amet',
        ], $element->getData());
    }
    
    public function testExtractorNotShift()
    {
        $extract = new ArrayPropertyExtractor('bar', false);
        $element = $extract('foo', [
            'lorem' => 'ipsum',
            'bar'   => 'baz',
            'dolor' => 'sit amet',
        ]);
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertEquals('baz', $element->getId());
        $this->assertEquals([
            'lorem' => 'ipsum',
            'bar'   => 'baz',
            'dolor' => 'sit amet',
        ], $element->getData());
    }
}
