<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;
use BenTools\ETL\EventDispatcher\Event\TransformEvent;
use BenTools\ETL\Transformer\CallableBatchTransformer;
use Generator;

use function strtoupper;

it('transforms items in batches', function () {
    // Given
    $items = ['foo', 'bar', 'baz', 'qux'];

    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            fn (array $items) => array_map(strtoupper(...), $items),
        ))
        ->withOptions(new EtlConfiguration(batchSize: 2));

    // When
    $report = $executor->process($items);

    // Then
    expect($report->output)->toBe(['FOO', 'BAR', 'BAZ', 'QUX']);
});

it('handles partial last batch', function () {
    // Given
    $items = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];

    $batchSizes = [];
    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            function (array $items) use (&$batchSizes) {
                $batchSizes[] = count($items);

                return array_map(strtoupper(...), $items);
            },
        ))
        ->withOptions(new EtlConfiguration(batchSize: 3));

    // When
    $report = $executor->process($items);

    // Then
    expect($report->output)->toBe(['A', 'B', 'C', 'D', 'E', 'F', 'G']);
    expect($batchSizes)->toBe([3, 3, 1]);
});

it('handles empty input', function () {
    // Given
    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            fn (array $items) => array_map(strtoupper(...), $items),
        ))
        ->withOptions(new EtlConfiguration(batchSize: 5));

    // When
    $report = $executor->process([]);

    // Then
    expect($report->output)->toBeNull();
});

it('skips items during extract phase', function () {
    // Given
    $items = ['foo', 'bar', 'baz', 'qux'];

    $batchSizes = [];
    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            function (array $items) use (&$batchSizes) {
                $batchSizes[] = count($items);

                return array_map(strtoupper(...), $items);
            },
        ))
        ->onExtract(function (ExtractEvent $event) {
            if ('bar' === $event->item) {
                $event->state->skip();
            }
        })
        ->withOptions(new EtlConfiguration(batchSize: 2));

    // When
    $report = $executor->process($items);

    // Then - 'bar' was skipped, so batch 1 only has 'foo', batch 2 has 'baz'+'qux'
    expect($report->output)->toBe(['FOO', 'BAZ', 'QUX']);
    expect($batchSizes)->toBe([1, 2]);
});

it('stops processing during extract phase', function () {
    // Given
    $items = ['foo', 'bar', 'baz', 'qux'];

    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            fn (array $items) => array_map(strtoupper(...), $items),
        ))
        ->onExtract(function (ExtractEvent $event) {
            if ('baz' === $event->item) {
                $event->state->stop();
            }
        })
        ->withOptions(new EtlConfiguration(batchSize: 4));

    // When
    $report = $executor->process($items);

    // Then - stopped at 'baz', only 'foo' and 'bar' were processed
    expect($report->output)->toBe(['FOO', 'BAR']);
});

it('skips items during transform phase', function () {
    // Given
    $items = ['foo', 'bar', 'baz'];

    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            fn (array $items) => array_map(strtoupper(...), $items),
        ))
        ->onTransform(function (TransformEvent $event) {
            if ('BAR' === [...$event->transformResult][0]) {
                $event->state->skip();
            }
        })
        ->withOptions(new EtlConfiguration(batchSize: 3));

    // When
    $report = $executor->process($items);

    // Then
    expect($report->output)->toBe(['FOO', 'BAZ']);
});

it('supports fan-out in batch mode', function () {
    // Given
    $items = ['foo', 'bar'];

    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            function (array $items): Generator {
                foreach ($items as $item) {
                    yield $item;
                    yield strtoupper($item);
                }
            },
        ))
        ->withOptions(new EtlConfiguration(batchSize: 2));

    // When
    $report = $executor->process($items);

    // Then
    expect($report->output)->toBe(['foo', 'FOO', 'bar', 'BAR']);
});

it('works with batchSize of 1', function () {
    // Given
    $items = ['foo', 'bar'];

    $batchSizes = [];
    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            function (array $items) use (&$batchSizes) {
                $batchSizes[] = count($items);

                return array_map(strtoupper(...), $items);
            },
        ))
        ->withOptions(new EtlConfiguration(batchSize: 1));

    // When
    $report = $executor->process($items);

    // Then - batchSize=1 still uses batch path with chunks of 1
    expect($report->output)->toBe(['FOO', 'BAR']);
    expect($batchSizes)->toBe([1, 1]);
});

it('ignores batchSize when transformer is not BatchTransformerInterface', function () {
    // Given
    $items = ['foo', 'bar', 'baz'];

    $executor = (new EtlExecutor())
        ->transformWith(fn (mixed $item) => strtoupper($item))
        ->withOptions(new EtlConfiguration(batchSize: 2));

    // When
    $report = $executor->process($items);

    // Then - works normally, no batching
    expect($report->output)->toBe(['FOO', 'BAR', 'BAZ']);
});

it('works with flushFrequency', function () {
    // Given
    $items = ['a', 'b', 'c', 'd', 'e', 'f'];

    $flushCount = 0;
    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            fn (array $items) => array_map(strtoupper(...), $items),
        ))
        ->onFlush(function () use (&$flushCount) {
            ++$flushCount;
        })
        ->withOptions(new EtlConfiguration(batchSize: 3, flushEvery: 3));

    // When
    $report = $executor->process($items);

    // Then
    expect($report->output)->toBe(['A', 'B', 'C', 'D', 'E', 'F']);
    expect($flushCount)->toBe(3); // flush at 3 items + flush at 6 items + final flush
});

it('fires extract events for each item individually', function () {
    // Given
    $items = ['foo', 'bar', 'baz'];

    $extractedItems = [];
    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            fn (array $items) => array_map(strtoupper(...), $items),
        ))
        ->onExtract(function (ExtractEvent $event) use (&$extractedItems) {
            $extractedItems[] = $event->item;
        })
        ->withOptions(new EtlConfiguration(batchSize: 3));

    // When
    $executor->process($items);

    // Then - ExtractEvent fires per item, not per batch
    expect($extractedItems)->toBe(['foo', 'bar', 'baz']);
});

it('fires transform events for each result individually', function () {
    // Given
    $items = ['foo', 'bar'];

    $transformedItems = [];
    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            fn (array $items) => array_map(strtoupper(...), $items),
        ))
        ->onTransform(function (TransformEvent $event) use (&$transformedItems) {
            $transformedItems[] = [...$event->transformResult][0];
        })
        ->withOptions(new EtlConfiguration(batchSize: 2));

    // When
    $executor->process($items);

    // Then - TransformEvent fires per result, not per batch
    expect($transformedItems)->toBe(['FOO', 'BAR']);
});

it('handles batchSize larger than total items', function () {
    // Given
    $items = ['foo', 'bar'];

    $batchSizes = [];
    $executor = (new EtlExecutor())
        ->transformWith(new CallableBatchTransformer(
            function (array $items) use (&$batchSizes) {
                $batchSizes[] = count($items);

                return array_map(strtoupper(...), $items);
            },
        ))
        ->withOptions(new EtlConfiguration(batchSize: 100));

    // When
    $report = $executor->process($items);

    // Then - single batch with all items
    expect($report->output)->toBe(['FOO', 'BAR']);
    expect($batchSizes)->toBe([2]);
});
