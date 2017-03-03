<?php

namespace BenTools\ETL\Tests\Extractor;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Extractor\KeyValueExtractor;
use PHPUnit\Framework\TestCase;

class KeyValueExtractorTest extends TestCase
{

    public function testExtractor()
    {
        $extract = new KeyValueExtractor();
        $element = $extract('foo', 'bar');
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertEquals('foo', $element->getId());
        $this->assertEquals('bar', $element->getData());
    }
}
