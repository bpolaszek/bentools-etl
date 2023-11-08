<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Exception\ExtractException;
use RuntimeException;

it('throws an extract exception when it is thrown from the extractor', function () {
    $items = function () {
        yield 'foo';
        throw new ExtractException('Something bad happened.');
    };

    $executor = new EtlExecutor();
    $executor->process($items());
})->throws(ExtractException::class, 'Something bad happened.');

it('throws an extract exception when some other exception is thrown', function () {
    $items = function () {
        yield 'foo';
        throw new RuntimeException('Something bad happened.');
    };

    $executor = new EtlExecutor();
    $executor->process($items());
})->throws(ExtractException::class, 'Error during extraction.');
