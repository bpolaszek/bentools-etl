<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Exception\TransformException;
use RuntimeException;

use function expect;
use function it;

it('throws an extract exception when it is thrown from the extractor', function () {
    $items = ['foo', 'bar', 'baz'];
    $executor = (new EtlExecutor())->transformWith(function (mixed $value) {
        if ('bar' === $value) {
            throw new TransformException('Cannot transform `bar`.');
        }
        yield $value;
    });
    $executor->process($items);
})->throws(TransformException::class, 'Cannot transform `bar`.');

it('throws a transform exception when some other exception is thrown', function () {
    $items = ['foo', 'bar', 'baz'];
    $executor = (new EtlExecutor())->transformWith(function (mixed $value) {
        if ('bar' === $value) {
            throw new RuntimeException('Cannot transform `bar`.');
        }
        yield $value;
    });
    $executor->process($items);
})->throws(TransformException::class, 'Error during transformation.');

it('has stopped processing items, but has loaded the previous ones', function () {
    $items = ['foo', 'bar', 'baz'];
    $loadedItems = [];
    $executor = (new EtlExecutor())
        ->transformWith(function (mixed $value) {
            if ('bar' === $value) {
                throw new TransformException('Cannot transform `bar`.');
            }
            yield $value;
        })
        ->loadInto(function (mixed $value) use (&$loadedItems) {
            $loadedItems[] = $value;
        })
    ;
    try {
        $executor->process($items);
    } catch (TransformException) {
    }

    expect($loadedItems)->toBe(['foo']);
});
