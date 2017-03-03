<?php

namespace BenTools\ETL\Tests\Extractor;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Extractor\CallbackExtractor;
use PHPUnit\Framework\TestCase;

class CallbackExtractorTest extends TestCase
{

    public function testExtractor()
    {
        $callback = function (ContextElementInterface $element) {
            $data = $element->getData();
            $this->assertEquals('foo', $element->getId());
            $element->setId($data->bar);
        };
        $extract  = new CallbackExtractor($callback);
        $element  = $extract('foo', (object) [
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
}
