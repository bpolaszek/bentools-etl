<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Normalizer;

use Bentools\ETL\Normalizer\NumericStringToNumberNormalizer;

use function array_walk;
use function expect;

it('normalizes numeric strings to numbers', function () {
    $normalizer = new NumericStringToNumberNormalizer();

    // Given
    $strings = [
        'foo',
        '12345',
        '12345.67',
        '',
    ];

    // When
    array_walk($strings, fn (&$value) => $value = $normalizer->normalize($value));

    // Then
    expect($strings)->toBe(['foo', 12345, 12345.67, '']);
});
