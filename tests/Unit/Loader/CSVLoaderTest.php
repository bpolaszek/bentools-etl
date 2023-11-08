<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Loader\CSVLoader;
use SplFileObject;

use function array_combine;
use function dirname;
use function expect;
use function implode;
use function strtr;
use function sys_get_temp_dir;
use function uniqid;

it('loads items to a CSV file', function () {
    $cities = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $destination = 'file://'.sys_get_temp_dir().'/'.uniqid('10-biggest-cities_').'.csv';
    $executor = new EtlExecutor(loader: new CSVLoader($destination, ['columns' => 'auto']));
    $output = $executor->process($cities)->output;
    expect($output)->toBe($destination);

    // @phpstan-ignore-next-line
    $writtenContent = implode('', [...new SplFileObject($output, 'r')]);
    // @phpstan-ignore-next-line
    $expectedContent = implode('', [...new SplFileObject(dirname(__DIR__, 2).'/data/10-biggest-cities.csv', 'r')]);

    expect($writtenContent)->toBe($expectedContent);
});

it('loads items to a CSV string', function () {
    $cities = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $executor = new EtlExecutor(loader: new CSVLoader(options: ['columns' => 'auto']));
    $output = $executor->process($cities)->output;

    // @phpstan-ignore-next-line
    $expectedContent = implode('', [...new SplFileObject(dirname(__DIR__, 2).'/data/10-biggest-cities.csv', 'r')]);

    expect($output)->toBe($expectedContent);
});

it('can write specific columns', function () {
    $cities = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $initialColumns = [
        'city_english_name',
        'city_local_name',
        'country_iso_code',
        'continent',
        'population',
    ];
    $prettyColumns = [
        'CityEnglishName',
        'CityLocalName',
        'CountryIsoCode',
        'Continent',
        'Population',
    ];
    $executor = new EtlExecutor(loader: new CSVLoader(options: ['columns' => $prettyColumns]));
    $output = $executor->process($cities)->output;

    $expectedContent = strtr(
        implode('', [...new SplFileObject(dirname(__DIR__, 2).'/data/10-biggest-cities.csv', 'r')]), // @phpstan-ignore-line
        array_combine($initialColumns, $prettyColumns),
    );

    expect($output)->toBe($expectedContent);
});

it('can ignore columns', function () {
    $cities = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $executor = new EtlExecutor(loader: new CSVLoader());
    $output = $executor->process($cities)->output;

    $lines = [...new SplFileObject(dirname(__DIR__, 2).'/data/10-biggest-cities.csv', 'r')];
    unset($lines[0]);
    $expectedContent = implode('', $lines); // @phpstan-ignore-line

    expect($output)->toBe($expectedContent);
});
