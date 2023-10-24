<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Transformer;

use Bentools\ETL\EtlState;
use Bentools\ETL\Transformer\CallableTransformer;

use function strtoupper;

it('converts a callable to a transformer', function () {
    // Given
    $state = new EtlState();
    $transformer = new CallableTransformer(fn (mixed $value) => yield strtoupper($value));

    // When
    $transformed = $transformer->transform('foo', $state);

    // Then
    expect([...$transformed])->toBe(['FOO']);
});
