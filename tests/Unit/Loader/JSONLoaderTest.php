<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\Loader\JSONLoader;
use SplFileObject;

use function dataset;
use function dirname;
use function expect;
use function implode;
use function sys_get_temp_dir;
use function uniqid;

use const INF;

dataset('config', [
    new EtlConfiguration(flushEvery: INF),
    new EtlConfiguration(flushEvery: 3),
]);

it('loads items to a JSON file', function (EtlConfiguration $options) {
    $cities = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $destination = 'file://'.sys_get_temp_dir().'/'.uniqid('10-biggest-cities_').'.json';
    $executor = new EtlExecutor(loader: new JSONLoader($destination), options: $options);
    $output = $executor->process($cities)->output;
    expect($output)->toBe($destination);

    // @phpstan-ignore-next-line
    $writtenContent = implode('', [...new SplFileObject($output, 'r')]);
    // @phpstan-ignore-next-line
    $expectedContent = implode('', [...new SplFileObject(dirname(__DIR__, 2).'/data/10-biggest-cities.json', 'r')]);

    expect($writtenContent)->toBe($expectedContent);
})->with('config');

it('loads items to a JSON string', function (EtlConfiguration $options) {
    $cities = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $executor = new EtlExecutor(loader: new JSONLoader(), options: $options);
    $output = $executor->process($cities)->output;

    // @phpstan-ignore-next-line
    $expectedContent = implode('', [...new SplFileObject(dirname(__DIR__, 2).'/data/10-biggest-cities.json', 'r')]);

    expect($output)->toBe($expectedContent);
})->with('config');
