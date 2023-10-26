<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\EventDispatcher;

use Bentools\ETL\EventDispatcher\EventDispatcher;
use Bentools\ETL\EventDispatcher\PrioritizedListenerProvider;
use Bentools\ETL\EventDispatcher\StoppableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;

use function count;
use function expect;

trait EventVisitor
{
    use StoppableEventTrait;
    public array $visitors = []; // @phpstan-ignore-line

    public function visit(string $visitor): void
    {
        $this->visitors[] = $visitor;
        if (2 === count($this->visitors)) {
            $this->stopPropagation();
        }
    }
}

it('dispatches events, to the appropriate listeners, in the correct order', function () {
    $listenerProvider = new PrioritizedListenerProvider();
    $bus = new EventDispatcher($listenerProvider);
    $ignored = new class() {
        use EventVisitor;
    };
    $event = new class() {
        use EventVisitor;
    };

    // Given
    $listenerProvider->listenTo($event::class, fn (object $event) => $event->visit('A'));
    $listenerProvider->listenTo($event::class, fn (object $event) => $event->visit('B'), -1);
    $listenerProvider->listenTo($event::class, fn (object $event) => $event->visit('C'), 1);

    // When
    $dispatched = $bus->dispatch($event);

    // Then
    expect($dispatched)
        ->toBe($event)
        ->and($event->visitors)->toBe(['C', 'A', 'B'])
        ->and($ignored->visitors)->toBe([])
    ;
});

it('stops propagation of events', function () {
    $listenerProvider = new PrioritizedListenerProvider();
    $bus = new EventDispatcher($listenerProvider);
    $event = new class() implements StoppableEventInterface {
        use EventVisitor;
    };

    // Given
    $listenerProvider->listenTo($event::class, fn (object $event) => $event->visit('A'));
    $listenerProvider->listenTo($event::class, fn (object $event) => $event->visit('B'), -1);
    $listenerProvider->listenTo($event::class, fn (object $event) => $event->visit('C'), 1);

    // When
    $dispatched = $bus->dispatch($event);

    // Then
    expect($dispatched)
        ->toBe($event)
        ->and($event->visitors)->toBe(['C', 'A'])
    ;
});
