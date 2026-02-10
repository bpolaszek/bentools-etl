<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Transformer;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Transformer\ChainTransformer;
use Generator;

use function expect;
use function strrev;
use function strtoupper;

it('chains transformers with generator fan-out', function () {
    // Given
    $input = ['foo', 'bar'];

    $etl = (new EtlExecutor())
        ->transformWith(
            fn (string $item): string => strrev($item),
            function (string $item): Generator {
                yield $item;
                yield strtoupper($item);
            },
            fn (string $item): string => "({$item})",
        );

    // When
    $report = $etl->process($input);

    // Then
    expect($report->output)->toBe(['(oof)', '(OOF)', '(rab)', '(RAB)']);
});

it('chains transformers without generators', function () {
    // Given
    $input = ['foo', 'bar'];

    $etl = (new EtlExecutor())
        ->transformWith(
            fn (string $item): string => strrev($item),
            fn (string $item): string => strtoupper($item),
        );

    // When
    $report = $etl->process($input);

    // Then
    expect($report->output)->toBe(['OOF', 'RAB']);
});

it('chains transformers with generator fan-out using with()', function () {
    // Given
    $input = ['foo', 'bar'];

    $chain = (new ChainTransformer(fn (string $item): string => strrev($item)))
        ->with(function (string $item): Generator {
            yield $item;
            yield strtoupper($item);
        })
        ->with(fn (string $item): string => "({$item})");

    $etl = (new EtlExecutor())->transformWith($chain);

    // When
    $report = $etl->process($input);

    // Then
    expect($report->output)->toBe(['(oof)', '(OOF)', '(rab)', '(RAB)']);
});

it('returns self', function () {
    $chainTransformer = new ChainTransformer(fn () => null);
    expect(ChainTransformer::from($chainTransformer))->toBe($chainTransformer);
});
