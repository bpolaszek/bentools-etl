<?php

namespace BenTools\ETL\Tests\Extractor;

use BenTools\ETL\Context\ContextElement;
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
        $this->assertInstanceOf(ContextElement::class, $element);
        $this->assertEquals('foo', $element->getId());
        $this->assertEquals('bar', $element->getData());
    }

    public function testExtractorWithADifferentContextClass()
    {
        $context = new class() implements ContextElementInterface
        {
            public function setId($id): void {}
            public function getId() {}
            public function setData($data): void {}
            public function getData() {}
            public function skip(): void {}
            public function stop(bool $flush = true): void {}
            public function flush(): void {}
            public function shouldSkip(): bool {}
            public function shouldStop(): bool {}
            public function shouldFlush(): bool {}
        };

        $class = get_class($context);

        // Check constructor
        $extract = new KeyValueExtractor($class);
        $element = $extract('foo', 'bar');
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertNotInstanceOf(ContextElement::class, $element);
        $this->assertInstanceOf($class, $element);

        // Check setter
        $extract = new KeyValueExtractor();
        $extract->setClass($class);
        $element = $extract('foo', 'bar');
        $this->assertInstanceOf(ContextElementInterface::class, $element);
        $this->assertNotInstanceOf(ContextElement::class, $element);
        $this->assertInstanceOf($class, $element);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExtractorWithAnInvalidContextClass()
    {
        $context = new class() {};
        $class = get_class($context);
        $extract = new KeyValueExtractor($class);
        $extract('foo', 'bar');
    }
}
