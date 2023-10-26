<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader;

use Bentools\ETL\EtlExecutor;
use Bentools\ETL\Loader\JSONLoader;
use SplFileObject;

use function dirname;
use function expect;
use function implode;
use function sys_get_temp_dir;
use function uniqid;

it('loads items to a JSON file', function () {
    $cities = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $destination = 'file://'.sys_get_temp_dir().'/'.uniqid('10-biggest-cities_').'.json';
    $executor = new EtlExecutor(loader: new JSONLoader($destination));
    $output = $executor->process($cities)->output;
    expect($output)->toBe($destination);

    // @phpstan-ignore-next-line
    $writtenContent = implode('', [...new SplFileObject($output, 'r')]);
    // @phpstan-ignore-next-line
    $expectedContent = implode('', [...new SplFileObject(dirname(__DIR__, 2).'/data/10-biggest-cities.json', 'r')]);

    expect($writtenContent)->toBe($expectedContent);
});

it('loads items to a JSON string', function () {
    $cities = require dirname(__DIR__, 2).'/data/10-biggest-cities.php';
    $executor = new EtlExecutor(loader: new JSONLoader());
    $output = $executor->process($cities)->output;

    // @phpstan-ignore-next-line
    $expectedContent = implode('', [...new SplFileObject(dirname(__DIR__, 2).'/data/10-biggest-cities.json', 'r')]);

    expect($output)->toBe($expectedContent);
});
