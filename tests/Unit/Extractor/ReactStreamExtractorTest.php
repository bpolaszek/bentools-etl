<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Extractor\ReactStreamExtractor;
use Mockery;
use React\Stream\ReadableStreamInterface;

use function expect;

it('supports any readable stream', function () {
    $a = Mockery::mock(ReadableStreamInterface::class);
    $b = Mockery::mock(ReadableStreamInterface::class);

    $extractor = new ReactStreamExtractor($a);
    expect($extractor->extract(new EtlState(source: $b)))->toBe($b)
        ->and($extractor->extract(new EtlState()))->toBe($a);
});
