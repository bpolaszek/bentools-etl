<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Extractor\TextLinesExtractor;

use function expect;

it('extracts lines from a text', function (array $options, array $expected, bool $useConstructor) {
    $text = <<<EOF
foo


bar
EOF;

    $state = new EtlState(source: $useConstructor ? null : $text);
    $extractor = new TextLinesExtractor(content: $useConstructor ? $text : null, options: $options);

    // When
    $items = $extractor->extract($state);

    // Then
    expect([...$items])->toBe($expected);
})->with(function () {
    yield [
        'options' => ['skipEmptyLines' => true],
        'expected' => ['foo', 'bar'],
    ];
    yield [
        'options' => [],
        'expected' => ['foo', 'bar'],
    ];
    yield [
        'options' => ['skipEmptyLines' => false],
        'expected' => ['foo', '', '', 'bar'],
    ];
})->with(function () {
    yield ['useConstructor' => true];
    yield ['useConstructor' => false];
});

it('returns an empty iterator when the content is null', function () {
    $state = new EtlState();
    $extractor = new TextLinesExtractor();

    // When
    $items = $extractor->extract($state);

    expect([...$items])->toBe([]);
});
