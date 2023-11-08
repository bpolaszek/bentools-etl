<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Extractor\IterableExtractor;

use function expect;
use function it;

it('works', function () {
    $state = new EtlState();
    $extractor = new IterableExtractor(['foo', 'bar']);
    expect($extractor->extract($state))
        ->toBe(['foo', 'bar']);

    $state = new EtlState(source: ['bar', 'baz']);
    expect($extractor->extract($state))
        ->toBe(['bar', 'baz']);
});

it('yells whenever source is not iterable', function () {
    (new IterableExtractor())->extract(new EtlState(source: 'foo'));
})
    ->throws(ExtractException::class);
