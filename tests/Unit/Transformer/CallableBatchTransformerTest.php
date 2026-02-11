<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Transformer;

use BenTools\ETL\EtlState;
use BenTools\ETL\Transformer\CallableBatchTransformer;

use function strtoupper;

it('converts a callable to a batch transformer', function () {
    // Given
    $state = new EtlState();
    $transformer = new CallableBatchTransformer(
        fn (array $items) => array_map(strtoupper(...), $items),
    );

    // When
    $transformed = $transformer->transform(['foo', 'bar'], $state);

    // Then
    expect([...$transformed])->toBe(['FOO', 'BAR']);
});

it('handles single-item batches', function () {
    // Given
    $state = new EtlState();
    $transformer = new CallableBatchTransformer(
        fn (array $items) => array_map(strtoupper(...), $items),
    );

    // When
    $transformed = $transformer->transform(['foo'], $state);

    // Then
    expect([...$transformed])->toBe(['FOO']);
});

it('handles empty batches', function () {
    // Given
    $state = new EtlState();
    $transformer = new CallableBatchTransformer(
        fn (array $items) => array_map(strtoupper(...), $items),
    );

    // When
    $transformed = $transformer->transform([], $state);

    // Then
    expect([...$transformed])->toBe([]);
});
