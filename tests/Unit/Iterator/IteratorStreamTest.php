<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Iterator;

use BenTools\ETL\Iterator\IteratorStream;
use BenTools\ETL\Tests\Stubs\WritableStreamStub;
use React\EventLoop\Factory;
use React\EventLoop\Loop;

use function beforeEach;
use function expect;

beforeEach(fn () => Loop::set(Factory::create()));

it('is readable during iteration', function () {
    $items = ['foo', 'bar'];
    $stream = new IteratorStream($items);

    for ($i = 0; $i < 2; ++$i) {
        expect($stream->isReadable())->toBeTrue();
        $stream->iterator->consume();
    }

    expect($stream->isReadable())->toBeFalse();
    Loop::stop();
});

it('can be paused and resumed', function () {
    $stream = new IteratorStream([]);
    expect($stream->paused)->toBeFalse();

    // When
    $stream->pause();

    // Then
    expect($stream->paused)->toBeTrue();

    // When
    $stream->resume();

    // Then
    expect($stream->paused)->toBeFalse();
});

it('can pipe data', function () {
    $items = ['foo', 'bar', 'baz'];
    $stream = new IteratorStream($items);
    $dest = new WritableStreamStub();
    $stream->pipe($dest);

    // When
    Loop::run();

    // Then
    expect($dest->data)->toBe($items);
});
