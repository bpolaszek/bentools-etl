<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\StartEvent;

use function expect;

it('fires a start event', function () {
    $event = null;

    // Given
    $executor = (new EtlExecutor())
        ->onStart(function (StartEvent $e) use (&$event) {
            $event = $e;
            $e->state->stop();
        });

    // When
    $executor->process(['foo', 'bar']);

    // Then
    expect($event)->toBeInstanceOf(StartEvent::class)
        ->and($event->state->nbTotalItems)->toBe(2)
        ->and($event->state->nbLoadedItems)->toBe(0)
    ;
});
