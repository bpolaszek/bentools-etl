<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Transformer;

use Bentools\ETL\EtlExecutor;
use Bentools\ETL\Transformer\ChainTransformer;
use Generator;

use function expect;
use function implode;
use function strrev;
use function strtoupper;

it('chains transformers', function () {
    // Given
    $input = ['foo', 'bar'];
    $transformer = (new ChainTransformer(
        fn (string $item): string => strrev($item),
        function (string $item): Generator {
            yield $item;
            yield strtoupper($item);
        },
    ))
        ->with(fn (Generator $items): array => [...$items])
        ->with(fn (array $items): string => implode('-', $items));

    // When
    $report = (new EtlExecutor(transformer: $transformer))
        ->process($input);

    // Then
    expect($report->output)->toBe([
        'oof-OOF',
        'rab-RAB',
    ]);
});