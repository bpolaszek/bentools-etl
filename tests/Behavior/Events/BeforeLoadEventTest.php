<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\BeforeLoadEvent;

use function expect;
use function strtoupper;

it('fires a load event', function () {
    // Given
    $executor = (new EtlExecutor())->transformWith(function (mixed $value) {
        yield $value;
        yield strtoupper($value);
    })
        ->onBeforeLoad(function (BeforeLoadEvent $e) {
            match ($e->item) {
                'bar' => $e->state->skip(),
                'baz' => $e->state->stop(),
                default => null,
            };
        });

    // When
    $report = $executor->process(['foo', 'bar', 'baz']);

    // Then
    expect($report->output)->toHaveCount(3)
        ->and($report->output)->toBe(['foo', 'FOO', 'BAR']);
});
