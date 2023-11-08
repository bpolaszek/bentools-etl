<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Extractor\CallableExtractor;

it('converts a callable to an extractor', function () {
    // Given
    $state = new EtlState();
    $callable = fn () => ['foo', 'bar'];

    // When
    $value = (new CallableExtractor($callable))->extract($state);

    // Then
    expect($value)->toBe(['foo', 'bar']);
});
