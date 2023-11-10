<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Extractor\CallableExtractor;
use EmptyIterator;

use function expect;

it('converts a callable to an extractor', function () {
    // Given
    $state = new EtlState();
    $callable = fn () => ['foo', 'bar'];

    // When
    $value = (new CallableExtractor($callable))->extract($state);

    // Then
    expect($value)->toBe(['foo', 'bar']);
});

it('returns an empty iterable when extracted content is null', function () {
    // Given
    $state = new EtlState();
    $callable = fn () => null;

    // When
    $value = (new CallableExtractor($callable))->extract($state);

    // Then
    expect($value)->toBeInstanceOf(EmptyIterator::class);
});
it('returns an iterable of values when extracted content is not iterable', function () {
    // Given
    $state = new EtlState();
    $callable = fn () => 'foo';

    // When
    $value = (new CallableExtractor($callable))->extract($state);

    // Then
    expect($value)->toBe(['foo']);
});
