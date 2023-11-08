<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\LoadEvent;

use function expect;
use function strtoupper;

it('fires a load event', function () {
    $loadedItems = [];

    // Given
    $executor = (new EtlExecutor())->transformWith(function (mixed $value) {
        yield $value;
        yield strtoupper($value);
    })
        ->onLoad(function (LoadEvent $e) use (&$loadedItems) {
            $loadedItems[] = $e->item;
        });

    // When
    $executor->process([2 => 'foo', 3 => 'bar']);

    // Then
    expect($loadedItems)->toHaveCount(4)
        ->and($loadedItems)->toBe(['foo', 'FOO', 'bar', 'BAR']);
});
