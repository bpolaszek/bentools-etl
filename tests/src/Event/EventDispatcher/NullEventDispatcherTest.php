<?php

namespace BenTools\ETL\Tests\Event\EventDispatcher;

use BenTools\ETL\Event\ETLEvent;
use BenTools\ETL\Event\EventDispatcher\NullEventDispatcher;
use PHPUnit\Framework\TestCase;

class NullEventDispatcherTest extends TestCase
{

    public function testEventDispatcher()
    {
        $foo = false;
        $eventDispatcher = new NullEventDispatcher();
        $eventDispatcher->addListener('bar', function () use (&$foo) {
            $foo = true;
        });
        $eventDispatcher->trigger(new ETLEvent('bar'));
        $this->assertFalse($foo);
    }
}
