<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use ArrayObject;
use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\LoadEvent;
use BenTools\ETL\Tests\Stubs\InMemoryLoader;

use function expect;

it('does arbitrary stuff on next tick', function () {
    $loader = new InMemoryLoader();

    // Given
    $options = ['flushEvery' => 3];
    $etl = (new EtlExecutor(loader: $loader, options: new EtlConfiguration(...$options)))
        ->onExtract(function (ExtractEvent $event) {
            // Let's trigger an early flush after the NEXT item (apple)
            if ('banana' === $event->item) {
                $event->state->nextTick(fn (EtlState $state) => $state->flush());
            }
        })
    ;

    // When
    $report = $etl->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);

    // Then
    expect($report->output)->toBeArray()
        ->and($report->output)->toHaveCount(2)
        ->and($report->output[0])->toBe(['banana', 'apple'])
        ->and($report->output[1])->toBe(['strawberry', 'raspberry', 'peach']);
});

it('can trigger several callbacks, which are called only once', function () {
    // Given
    $bucket = new ArrayObject();
    $etl = (new EtlExecutor())
        ->onLoad(function (LoadEvent $event) use ($bucket) {
            if ('apple' === $event->item) {
                $event->state->nextTick(fn (EtlState $state) => $bucket->append('apple'));
                $event->state->nextTick(fn (EtlState $state) => $bucket->append('APPLE'));
            }
        });

    // When
    $report = $etl->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);

    // Then
    expect([...$bucket])->toBe(['apple', 'APPLE']);
});
