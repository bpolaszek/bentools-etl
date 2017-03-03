<?php

namespace BenTools\ETL\Tests\Event\EventDispatcher\Bridge;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Event\ContextElementEvent;
use PHPUnit\Framework\TestCase;

use BenTools\ETL\Event\EventDispatcher\Bridge\Symfony\SymfonyEvent;

class SymfonyEventTest extends TestCase
{

    public function testEventName()
    {
        $event = new SymfonyEvent(new ContextElementEvent('foo', new ContextElement('bar', 'baz')));
        $this->assertEquals('foo', $event->getName());
        $this->assertEquals('bar', $event->getElement()->getId());
        $this->assertEquals('baz', $event->getElement()->getData());
        return $event;
    }

    /**
     * @depends testEventName
     */
    public function testStopPropagation(SymfonyEvent $event)
    {
        $this->assertEquals(false, $event->isPropagationStopped());
        $event->stopPropagation();
        $this->assertEquals(true, $event->isPropagationStopped());
    }

}
