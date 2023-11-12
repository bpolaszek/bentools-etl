<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Extractor\ChainExtractor;

use function BenTools\ETL\extractFrom;
use function expect;

it('chains extractors', function () {
    // Given
    $extractor = (new ChainExtractor(
        fn () => 'banana',
        fn () => yield from ['apple', 'strawberry'],
    ))->with(fn () => ['raspberry', 'peach']);
    $executor = (new EtlExecutor($extractor));

    // When
    $report = $executor->process();

    // Then
    expect($report->output)->toBe(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);
});

it('silently chains extractors', function () {
    // Given
    $executor = extractFrom(
        fn () => 'banana',
        fn () => yield from ['apple', 'strawberry'],
        fn () => ['raspberry', 'peach']
    );

    // When
    $report = $executor->process();

    // Then
    expect($report->output)->toBe(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);
});
