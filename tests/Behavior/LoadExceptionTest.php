<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use BenTools\ETL\Exception\LoadException;
use RuntimeException;

use function BenTools\ETL\loadInto;
use function expect;
use function it;

it('throws a load exception when it is thrown from the extractor', function () {
    $items = ['foo', 'bar', 'baz'];
    $executor = loadInto(function (mixed $value) {
        if ('bar' === $value) {
            throw new LoadException('Cannot load `bar`.');
        }
    });
    $executor->process($items);
})->throws(LoadException::class, 'Cannot load `bar`.');

it('throws a load exception when some other exception is thrown', function () {
    $items = ['foo', 'bar', 'baz'];
    $executor = loadInto(function (mixed $value) {
        if ('bar' === $value) {
            throw new RuntimeException('Cannot load `bar`.');
        }
    });
    $executor->process($items);
})->throws(LoadException::class, 'Error during loading.');

it('has stopped processing items, but has loaded the previous ones', function () {
    $items = ['foo', 'bar', 'baz'];
    $loadedItems = [];
    $executor = loadInto(function (mixed $value) use (&$loadedItems) {
        if ('bar' === $value) {
            throw new LoadException('Cannot load `bar`.');
        }
        $loadedItems[] = $value;
    })
    ;
    try {
        $executor->process($items);
    } catch (LoadException) {
    }

    expect($loadedItems)->toBe(['foo']);
});
