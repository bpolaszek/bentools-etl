<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Extractor\ReactStreamExtractorProcessor;
use React\EventLoop\Loop;
use React\Stream\ReadableResourceStream;
use RuntimeException;

use function BenTools\ETL\withRecipe;
use function expect;
use function fopen;

it('processes React streams', function () {
    // Given
    $stream = new ReadableResourceStream(fopen('php://temp', 'rb'));
    Loop::futureTick(fn () => $stream->emit('data', ['hello']));
    Loop::futureTick(fn () => $stream->emit('data', ['world']));
    $executor = withRecipe(new ReactStreamExtractorProcessor());

    // When
    $state = $executor->process($stream);

    // Then
    expect($state->output)->toBe(['hello', 'world']);
});

it('can skip items and stop the workflow', function () {
    // Given
    $stream = new ReadableResourceStream(fopen('php://temp', 'rb'));
    $fruits = ['banana', 'apple', 'strawberry', 'raspberry', 'peach'];
    foreach ($fruits as $fruit) {
        Loop::futureTick(fn () => $stream->emit('data', [$fruit]));
    }
    $executor = withRecipe(new ReactStreamExtractorProcessor())
        ->onExtract(function (ExtractEvent $event) {
            match ($event->item) {
                'apple' => $event->state->skip(),
                'peach' => $event->state->stop(),
                default => null,
            };
        })
    ;

    // When
    $state = $executor->process($stream);

    // Then
    expect($state->output)->toBe(['banana', 'strawberry', 'raspberry']);
});

it('throws ExtractExceptions', function () {
    // Given
    $stream = new ReadableResourceStream(fopen('php://temp', 'rb'));
    Loop::futureTick(fn () => $stream->emit('data', ['hello']));
    $executor = withRecipe(new ReactStreamExtractorProcessor())
        ->onExtract(fn () => throw new RuntimeException());

    // When
    $executor->process($stream);
})->throws(ExtractException::class);
