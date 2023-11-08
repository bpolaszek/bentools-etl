<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\Tests\InMemoryLoader;

use function expect;

it('flushes items at the right time', function () {
    $loader = new InMemoryLoader();

    // Given
    $etl = new EtlExecutor(loader: $loader, options: new EtlConfiguration(flushEvery: 2));

    // When
    $report = $etl->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);

    // Then
    expect($report->output)->toBeArray()
        ->and($report->output)->toHaveCount(3)
        ->and($report->output[0])->toBe(['banana', 'apple'])
        ->and($report->output[1])->toBe(['strawberry', 'raspberry'])
        ->and($report->output[2])->toBe(['peach']);
});

it('forces flushes', function () {
    $loader = new InMemoryLoader();

    // Given
    $etl = (new EtlExecutor(loader: $loader, options: new EtlConfiguration(flushEvery: 2)))
        ->onExtract(function (ExtractEvent $event) {
            if (0 === $event->state->currentItemIndex) {
                $event->state->flush();
            }
        });

    // When
    $report = $etl->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);

    // Then
    expect($report->output)->toBeArray()
        ->and($report->output)->toHaveCount(3)
        ->and($report->output[0])->toBe(['banana'])
        ->and($report->output[1])->toBe(['apple', 'strawberry'])
        ->and($report->output[2])->toBe(['raspberry', 'peach']);
});
