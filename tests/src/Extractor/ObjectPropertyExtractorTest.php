<?php

namespace BenTools\ETL\Tests\Extractor;

use BenTools\ETL\Context\ContextElementInterface;
use PHPUnit\Framework\TestCase;

use BenTools\ETL\Extractor\ObjectPropertyExtractor;

class ObjectPropertyExtractorTest extends TestCase
{
    public function testExtractor()
    {
        $extract = new ObjectPropertyExtractor('bar');
        $element = $extract('foo', (object) [
            'lorem' => 'ipsum',
            'bar'   => 'baz',
            'dolor' => 'sit amet',
        ]);
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertEquals('baz', $element->getId());
        $this->assertEquals((object) [
            'lorem' => 'ipsum',
            'bar'   => 'baz',
            'dolor' => 'sit amet',
        ], $element->getData());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExtractorWithNonExistentProperty()
    {

        $extract = new ObjectPropertyExtractor('bar');
        $extract('foo', new \stdClass());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExtractorWithAnInvalidContextClass()
    {
        $context = new class() {};
        $class = get_class($context);
        $extract = new ObjectPropertyExtractor('bar', $class);
        $extract('foo', (object) ['bar' => 'baz']);
    }
}
