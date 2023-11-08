<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\InitEvent;

use function expect;

it('fires an init event', function () {
    $event = null;

    // Given
    $executor = (new EtlExecutor())
        ->onInit(function (InitEvent $e) use (&$event) {
            $event = $e;
            $e->state->stop();
        });

    // When
    $executor->process('sourceArgs', 'destArgs');

    // Then
    expect($event)->toBeInstanceOf(InitEvent::class)
        ->and($event->state->source)->toBe('sourceArgs')
        ->and($event->state->destination)->toBe('destArgs');
});
