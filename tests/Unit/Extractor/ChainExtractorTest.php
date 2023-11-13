<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Extractor\CallableExtractor;

use function BenTools\ETL\chain;
use function BenTools\ETL\extractFrom;
use function expect;

it('chains extractors', function () {
    // Given
    $executor = new EtlExecutor(new CallableExtractor(fn () => 'banana'));
    $executor = $executor->extractFrom(chain($executor->extractor)
        ->with(fn () => yield from ['apple', 'strawberry'])
        ->with(fn () => ['raspberry', 'peach']))
    ;

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
