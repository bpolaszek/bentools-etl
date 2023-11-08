<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit;

use function BenTools\ETL\array_fill_from;
use function expect;

it('produces a new array with the provided keys', function () {
    // Given
    $food = [
        'a' => 'Apple',
        'b' => 'Banana',
        'c' => 'Carrot',
        'd' => 'Dill',
    ];

    // When
    $result = array_fill_from(['a', 'b', 'e'], $food, ['b' => 'banana', 'f' => 'Fig']);

    // Then
    expect($result)->toBe([
        'a' => 'Apple',
        'b' => 'banana',
    ]);
});
