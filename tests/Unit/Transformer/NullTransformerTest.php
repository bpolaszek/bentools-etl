<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Transformer;

use Bentools\ETL\EtlState;
use Bentools\ETL\Transformer\NullTransformer;

use function expect;

it('yields the value as-is', function () {
    // Given
    $state = new EtlState();
    $transformer = new NullTransformer();

    // When
    $transformedItems = $transformer->transform('foo', $state);

    // Then
    expect($transformedItems)->toBe('foo');
});
