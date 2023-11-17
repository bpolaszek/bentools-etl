<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Recipe;

use BenTools\ETL\EventDispatcher\Event\BeforeLoadEvent;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\LoadEvent;
use BenTools\ETL\Recipe\FilterRecipe;
use InvalidArgumentException;

use function BenTools\ETL\skipWhen;
use function BenTools\ETL\withRecipe;
use function expect;
use function sprintf;
use function str_contains;
use function strtoupper;

it('filters items (on an exclude-list basis)', function (?string $eventClass, array $expectedResult) {
    // Given
    $skipItems = ['apple', 'BANANA'];
    $executor = withRecipe(
        skipWhen(
            fn ($item) => !in_array($item, $skipItems, true),
            $eventClass,
        ),
    )
        ->transformWith(fn ($item) => strtoupper($item));

    // When
    $report = $executor->process(['banana', 'apple', 'strawberry', 'BANANA', 'APPLE', 'STRAWBERRY']);

    // Then
    expect($report->output)->toBe($expectedResult);
})->with(function () {
    yield [null, ['APPLE', 'BANANA']];
    yield [ExtractEvent::class, ['APPLE', 'BANANA']];
    yield [BeforeLoadEvent::class, ['BANANA', 'BANANA']];
});

it('filters items (on an allow-list basis)', function (?string $eventClass, array $expectedResult) {
    // Given
    $executor = withRecipe(
        new FilterRecipe(
            fn (string $item) => str_contains($item, 'b') || str_contains($item, 'B'),
        ),
    )
        ->transformWith(fn ($item) => strtoupper($item));

    // When
    $report = $executor->process(['banana', 'apple', 'strawberry', 'BANANA', 'APPLE', 'STRAWBERRY']);

    // Then
    expect($report->output)->toBe($expectedResult);
})->with(function () {
    yield [null, ['BANANA', 'STRAWBERRY', 'BANANA', 'STRAWBERRY']];
    yield [ExtractEvent::class, ['BANANA', 'STRAWBERRY', 'BANANA', 'STRAWBERRY']];
    yield [BeforeLoadEvent::class, ['BANANA', 'STRAWBERRY', 'BANANA', 'STRAWBERRY']];
});

it('does not accept other types of events', function () {
    new FilterRecipe(fn () => '', LoadEvent::class);
})->throws(
    InvalidArgumentException::class,
    sprintf('Can only filter on ExtractEvent / LoadEvent, not %s', LoadEvent::class),
);
