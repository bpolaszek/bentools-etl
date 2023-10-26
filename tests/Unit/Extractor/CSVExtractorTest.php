<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use Bentools\ETL\EtlState;
use Bentools\ETL\Exception\ExtractException;
use Bentools\ETL\Extractor\CSVExtractor;

use function dirname;
use function expect;
use function Safe\file_get_contents;

it('yells if source is not a string', function () {
    $state = new EtlState(source: [13.37]);
    $extractor = new CSVExtractor();

    $extractor->extract($state);
})->throws(ExtractException::class);

it('iterates over a string containing CSV data', function () {
    $state = new EtlState();
    $content = file_get_contents(dirname(__DIR__, 2).'/data/10-biggest-cities.csv');
    $expected = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $extractor = new CSVExtractor($content, ['columns' => 'auto']);

    // When
    $extractedItems = [...$extractor->extract($state)];

    // Then
    expect($extractedItems)->toBe($expected);
});

it('iterates over a file containing CSV data', function () {
    $extractor = new CSVExtractor(options: ['columns' => 'auto']);

    // When
    $state = new EtlState(source: 'file://'.dirname(__DIR__, 2).'/data/10-biggest-cities.csv');
    $extractedItems = [...$extractor->extract($state)];

    // Then
    expect($extractedItems)->toHaveCount(10)
        ->and($extractedItems[0]['city_english_name'] ?? null)->toBe('New York')
        ->and($extractedItems[9]['city_english_name'] ?? null)->toBe('London');
});
