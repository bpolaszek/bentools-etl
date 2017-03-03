<?php

namespace BenTools\ETL\Tests\Event;

use PHPUnit\Framework\TestCase;

use BenTools\ETL\Event\ETLEvent;

class ETLEventTest extends TestCase
{

    public function testEventName()
    {
        $event = new ETLEvent('foo');
        $this->assertEquals('foo', $event->getName());
        return $event;
    }

    /**
     * @depends testEventName
     */
    public function testStopPropagation(ETLEvent $event)
    {
        $this->assertEquals(false, $event->isPropagationStopped());
        $event->stopPropagation();
        $this->assertEquals(true, $event->isPropagationStopped());
    }



}
