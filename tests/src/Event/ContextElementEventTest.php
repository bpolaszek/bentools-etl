<?php

namespace BenTools\ETL\Tests\Event;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Event\ContextElementEvent;
use PHPUnit\Framework\TestCase;

class ContextElementEventTest extends TestCase
{

    public function testEventName()
    {
        $event = new ContextElementEvent('foo', new ContextElement('bar', 'baz'));
        $this->assertEquals('foo', $event->getName());
        $this->assertEquals('bar', $event->getElement()->getId());
        $this->assertEquals('baz', $event->getElement()->getData());
        return $event;
    }

    /**
     * @depends testEventName
     */
    public function testStopPropagation(ContextElementEvent $event)
    {
        $this->assertEquals(false, $event->isPropagationStopped());
        $event->stopPropagation();
        $this->assertEquals(true, $event->isPropagationStopped());
    }

}
