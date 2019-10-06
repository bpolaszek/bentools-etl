<?php

namespace BenTools\ETL\Tests\EventDispatcher;

use BenTools\ETL\EventDispatcher\Event\EtlEvent;
use BenTools\ETL\EventDispatcher\EventDispatcher;
use BenTools\ETL\EventDispatcher\EventListener;
use PHPUnit\Framework\TestCase;
use function BenTools\ETL\Tests\dummy_etl;

class EventDispatcherTest extends TestCase
{

    /**
     * @test
     */
    public function it_successfully_dispatches_an_event()
    {
        $event = new class extends EtlEvent
        {
            public function __construct()
            {
                parent::__construct(dummy_etl());
            }

            public function getName(): string
            {
                return 'foo';
            }

        };

        $stack = [];

        $dispatcher = new EventDispatcher([
            new EventListener('foo', function () use (&$stack) {
                $stack[] = 'foo';
            }),
            new EventListener('foo', function () use (&$stack) {
                $stack[] = 'bar';
            }),
            new EventListener('bar', function () use (&$stack) {
                $stack[] = 'baz';
            }),
        ]);

        $dispatcher->dispatch($event);

        $this->assertEquals(['foo', 'bar'], $stack);
    }

    /**
     * @test
     */
    public function it_knows_how_to_handle_priorities()
    {
        $event = new class extends EtlEvent
        {
            public function __construct()
            {
                parent::__construct(dummy_etl());
            }

            public function getName(): string
            {
                return 'foo';
            }

        };

        $stack = [];

        $dispatcher = new EventDispatcher([
            new EventListener('foo', function () use (&$stack) {
                $stack[] = 'foo';
            }, -50),
            new EventListener('foo', function () use (&$stack) {
                $stack[] = 'bar';
            }, 100),
            new EventListener('foo', function () use (&$stack) {
                $stack[] = 'baz';
            }),
        ]);

        $dispatcher->dispatch($event);

        $this->assertEquals(['bar', 'baz', 'foo'], $stack);
    }

    /**
     * @test
     */
    public function it_stops_propagation_when_asked_to()
    {
        $event = new class extends EtlEvent
        {
            public function __construct()
            {
                parent::__construct(dummy_etl());
            }

            public function getName(): string
            {
                return 'foo';
            }

        };

        $stack = [];

        $dispatcher = new EventDispatcher([
            new EventListener('foo', function () use (&$stack) {
                $stack[] = 'foo';
            }, -50),
            new EventListener('foo', function () use (&$stack) {
                $stack[] = 'bar';
            }, 100),
            new EventListener('foo', function (EtlEvent $event) use (&$stack) {
                $stack[] = 'baz';
                $event->stopPropagation();
            }),
        ]);

        $dispatcher->dispatch($event);

        $this->assertEquals(['bar', 'baz'], $stack);
    }
}
