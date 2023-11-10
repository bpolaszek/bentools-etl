<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\LoadException;
use BenTools\ETL\Loader\CallableLoader;

it('converts a callable to a loader', function () {
    $items = [];

    // Given
    $state = new EtlState();
    $loader = new CallableLoader(function (mixed $item) use (&$items) {
        $items[] = $item;

        return $item;
    });

    // When
    $loader->load('foo', $state);
    $output = $loader->flush(false, $state);

    // Then
    expect($output)->toBe(['foo']);
});

it('complains if inner loader is not callable', function () {
    // Given
    $state = new EtlState();
    $loader = new CallableLoader();
    $loader->load('foo', $state);
})->throws(LoadException::class, 'Invalid destination.');
