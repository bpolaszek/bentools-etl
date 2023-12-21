<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Iterator;

use BenTools\ETL\Iterator\CSVIterator;
use BenTools\ETL\Iterator\StrTokIterator;
use SplFileObject;

use function dirname;
use function expect;
use function Safe\file_get_contents;

it('iterates over CSV data', function (CSVIterator $iterator) {
    $rows = [...$iterator];

    expect($rows)->toHaveCount(11)
        ->and($rows[0])->toBe([
            0 => 'city_english_name',
            1 => 'city_local_name',
            2 => 'country_iso_code',
            3 => 'continent',
            4 => 'population',
        ])
        ->and($rows[3])->toBe([
            0 => 'Tokyo',
            1 => '東京',
            2 => 'JP',
            3 => 'Asia',
            4 => 13929286,
        ]);
})->with(function () {
    $filename = dirname(__DIR__, 2).'/Data/10-biggest-cities.csv';
    yield 'string content' => new CSVIterator(new StrTokIterator(file_get_contents($filename)));
    yield 'file' => new CSVIterator(new SplFileObject($filename));
});

it('can make columns automatically', function (CSVIterator $iterator) {
    $rows = [...$iterator];

    expect($rows)->toHaveCount(10)
        ->and($rows[0])->toBe([
            'city_english_name' => 'New York',
            'city_local_name' => 'New York',
            'country_iso_code' => 'US',
            'continent' => 'North America',
            'population' => 8537673,
        ])
        ->and($rows[2])->toBe([
            'city_english_name' => 'Tokyo',
            'city_local_name' => '東京',
            'country_iso_code' => 'JP',
            'continent' => 'Asia',
            'population' => 13929286,
        ]);
})->with(function () {
    $filename = dirname(__DIR__, 2).'/Data/10-biggest-cities.csv';
    yield 'string content' => new CSVIterator(new StrTokIterator(file_get_contents($filename)), ['columns' => 'auto']);
    yield 'file' => new CSVIterator(new SplFileObject($filename), ['columns' => 'auto']);
});

it('can map user-defined columns', function (CSVIterator $iterator) {
    $rows = [...$iterator];

    expect($rows[1])->toBe([
        'cityEnglishName' => 'New York',
        'cityLocalName' => 'New York',
        'countryIsoCode' => 'US',
        'continent' => 'North America',
        'population' => 8537673,
    ])
        ->and($rows[3])->toBe([
            'cityEnglishName' => 'Tokyo',
            'cityLocalName' => '東京',
            'countryIsoCode' => 'JP',
            'continent' => 'Asia',
            'population' => 13929286,
        ]);
})->with(function () {
    $columns = [
        'cityEnglishName',
        'cityLocalName',
        'countryIsoCode',
        'continent',
        'population',
    ];
    $filename = dirname(__DIR__, 2).'/Data/10-biggest-cities.csv';
    yield 'string content' => new CSVIterator(new StrTokIterator(file_get_contents($filename)), ['columns' => $columns]);
    yield 'file' => new CSVIterator(new SplFileObject($filename), ['columns' => $columns]);
});

it('adds fields when the row has not enough columns', function (CSVIterator $iterator) {
    $rows = [...$iterator];

    expect($rows[1])->toBe([
        'cityEnglishName' => 'New York',
        'cityLocalName' => 'New York',
        'countryIsoCode' => 'US',
        'continent' => 'North America',
        'population' => 8537673,
        'misc' => null,
    ])
        ->and($rows[3])->toBe([
            'cityEnglishName' => 'Tokyo',
            'cityLocalName' => '東京',
            'countryIsoCode' => 'JP',
            'continent' => 'Asia',
            'population' => 13929286,
            'misc' => null,
        ]);
})->with(function () {
    $columns = [
        'cityEnglishName',
        'cityLocalName',
        'countryIsoCode',
        'continent',
        'population',
        'misc',
    ];
    $filename = dirname(__DIR__, 2).'/Data/10-biggest-cities.csv';
    yield 'string content' => new CSVIterator(new StrTokIterator(file_get_contents($filename)), ['columns' => $columns]);
    yield 'file' => new CSVIterator(new SplFileObject($filename), ['columns' => $columns]);
});

it('removes extra data whenever there are more fields than columns', function (CSVIterator $iterator) {
    $rows = [...$iterator];

    expect($rows[1])->toBe([
        'cityEnglishName' => 'New York',
        'cityLocalName' => 'New York',
        'countryIsoCode' => 'US',
        'continent' => 'North America',
    ])
        ->and($rows[3])->toBe([
            'cityEnglishName' => 'Tokyo',
            'cityLocalName' => '東京',
            'countryIsoCode' => 'JP',
            'continent' => 'Asia',
        ]);
})->with(function () {
    $columns = [
        'cityEnglishName',
        'cityLocalName',
        'countryIsoCode',
        'continent',
    ];
    $filename = dirname(__DIR__, 2).'/Data/10-biggest-cities.csv';
    yield 'string content' => new CSVIterator(new StrTokIterator(file_get_contents($filename)), ['columns' => $columns]);
    yield 'file' => new CSVIterator(new SplFileObject($filename), ['columns' => $columns]);
});
