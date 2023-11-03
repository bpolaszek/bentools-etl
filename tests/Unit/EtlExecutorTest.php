<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;

use function expect;
use function strtoupper;

it('basically works', function (callable $transformer) {
    $items = [];

    // Given
    $etl = (new EtlExecutor())
        ->extractFrom(fn () => yield from ['foo', 'bar'])
        ->transformWith($transformer)
        ->loadInto(function (string $item) use (&$items) {
            $items[] = $item;
        })
        ->withOptions(new EtlConfiguration(flushEvery: 1));

    // When
    $report = $etl->process();

    // Then
    expect($items)->toBe(['FOO', 'BAR'])
        ->and($report->nbTotalItems)->toBe(2)
        ->and($report->nbLoadedItems)->toBe(2)
        ->and($report->getDuration())->toBeBetween(0, 1)
    ;
})->with(function () {
    yield 'Return value' => fn (mixed $value) => strtoupper($value);
    yield 'Generator' => fn (mixed $value) => yield strtoupper($value);
});
