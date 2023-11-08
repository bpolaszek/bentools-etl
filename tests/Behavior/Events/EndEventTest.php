<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\EndEvent;

use function expect;

it('fires an end event', function () {
    $event = null;

    // Given
    $executor = (new EtlExecutor())
        ->onEnd(function (EndEvent $e) use (&$event) {
            $event = $e;
        });

    // When
    $report = $executor->process(['foo', 'bar']);

    // Then
    expect($event)->toBeInstanceOf(EndEvent::class)
        ->and($report->nbTotalItems)->toBe(2)
        ->and($report->nbLoadedItems)->toBe(2)
    ;
});
