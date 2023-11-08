<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Normalizer;

use BenTools\ETL\Normalizer\EmptyStringToNullNormalizer;

use function array_walk;
use function expect;

it('normalizes empty strings to nulls', function () {
    $normalizer = new EmptyStringToNullNormalizer();

    // Given
    $strings = [
        'foo',
        '',
    ];

    // When
    array_walk($strings, fn (&$value) => $value = $normalizer->normalize($value));

    // Then
    expect($strings)->toBe(['foo', null]);
});
