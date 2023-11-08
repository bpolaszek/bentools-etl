<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Extractor\JSONExtractor;
use stdClass;

use function dirname;
use function expect;
use function file_get_contents;

it('extracts JSON data', function (mixed $source, bool $useConstructor) {
    $expected = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $state = new EtlState(source: $useConstructor ? null : $source);
    $extractor = new JSONExtractor(source: $useConstructor ? $source : null);

    // When
    $items = $extractor->extract($state);

    // Then
    expect([...$items])->toBe(null === $source ? [] : $expected);
})->with(function () {
    $source = dirname(__DIR__, 2).'/data/10-biggest-cities.json';
    $content = file_get_contents($source);
    yield ['source' => 'file://'.$source];
    yield ['source' => $content];
    yield ['source' => null];
})->with(function () {
    yield ['useConstructor' => true];
    yield ['useConstructor' => false];
});

it('complains if content cannot be extracted', function () {
    [...(new JSONExtractor())->extract(new EtlState(source: new stdClass()))];
})->throws(ExtractException::class);
