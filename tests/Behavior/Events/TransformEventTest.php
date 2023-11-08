<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\TransformEvent;

use function expect;
use function strtoupper;

it('fires a transform event', function () {
    $transformedItems = [];

    // Given
    $executor = (new EtlExecutor())->transformWith(function (mixed $value) {
        yield $value;
        yield strtoupper($value);
    })
        ->onTransform(function (TransformEvent $e) use (&$transformedItems) {
            $transformedItems = [...$transformedItems, ...$e->items];
        });

    // When
    $executor->process([2 => 'foo', 3 => 'bar']);

    // Then
    expect($transformedItems)->toHaveCount(4)
        ->and($transformedItems)->toBe(['foo', 'FOO', 'bar', 'BAR']);
});
