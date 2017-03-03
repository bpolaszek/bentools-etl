<?php

namespace BenTools\ETL\Tests\Context;

use PHPUnit\Framework\TestCase;

use BenTools\ETL\Context\ContextElement;

class ContextElementTest extends TestCase
{

    /**
     * @var ContextElement
     */
    private $element;

    protected function setUp()
    {
        $this->element = new ContextElement('foo', 'bar');
    }

    public function testInit()
    {
        $this->assertEquals('foo', $this->element->getId());
        $this->assertEquals('bar', $this->element->getData());
        $this->assertEquals(false, $this->element->shouldSkip());
        $this->assertEquals(false, $this->element->shouldStop());
        $this->assertEquals(false, $this->element->shouldFlush());
    }

    public function testChangeIdAndData()
    {
        $this->element->setId('bar');
        $this->element->setData('baz');
        $this->assertEquals('bar', $this->element->getId());
        $this->assertEquals('baz', $this->element->getData());
        $this->assertEquals(false, $this->element->shouldSkip());
        $this->assertEquals(false, $this->element->shouldStop());
        $this->assertEquals(false, $this->element->shouldFlush());
    }

    public function testSkip()
    {
        $this->element->skip();
        $this->assertEquals('foo', $this->element->getId());
        $this->assertEquals('bar', $this->element->getData());
        $this->assertEquals(true,  $this->element->shouldSkip());
        $this->assertEquals(false, $this->element->shouldStop());
        $this->assertEquals(false, $this->element->shouldFlush());
    }

    public function testStopAndFlush()
    {
        $this->element->stop(true);
        $this->assertEquals('foo', $this->element->getId());
        $this->assertEquals('bar', $this->element->getData());    
        $this->assertEquals(false, $this->element->shouldSkip());
        $this->assertEquals(true,  $this->element->shouldStop());
        $this->assertEquals(true, $this->element->shouldFlush());
    }

    public function testStopWithoutFlushing()
    {
        $this->element->stop(false);
        $this->assertEquals('foo', $this->element->getId());
        $this->assertEquals('bar', $this->element->getData());    
        $this->assertEquals(false, $this->element->shouldSkip());
        $this->assertEquals(true,  $this->element->shouldStop());
        $this->assertEquals(false, $this->element->shouldFlush());
    }

    public function testFlush()
    {
        $this->element->flush();
        $this->assertEquals('foo', $this->element->getId());
        $this->assertEquals('bar', $this->element->getData());    
        $this->assertEquals(false, $this->element->shouldSkip());
        $this->assertEquals(false,  $this->element->shouldStop());
        $this->assertEquals(true, $this->element->shouldFlush());
    }
}
