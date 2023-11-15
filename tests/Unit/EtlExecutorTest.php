<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit;

use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\EventDispatcher\Event\FlushEvent;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Extractor\ExtractorProcessorInterface;
use BenTools\ETL\Loader\ConditionalLoaderInterface;
use LogicException;
use Pest\Exceptions\ShouldNotHappen;

use function expect;
use function strtoupper;

it('basically works', function (callable $transformer) {
    $items = [];

    // Given
    $etl = (new EtlExecutor())
        ->extractFrom(fn () => yield from ['foo', 'bar'])
        ->transformWith($transformer)
        ->loadInto(function (string $item) use (&$items) {
            $items[] = $item;
        })
        ->withOptions(new EtlConfiguration(flushEvery: 1));

    // When
    $report = $etl->process();

    // Then
    expect($items)->toBe(['FOO', 'BAR'])
        ->and($report->nbTotalItems)->toBe(2)
        ->and($report->nbLoadedItems)->toBe(2)
        ->and($report->getDuration())->toBeBetween(0, 1);
})->with(function () {
    yield 'Return value' => fn (mixed $value) => strtoupper($value);
    yield 'Generator' => fn (mixed $value) => yield strtoupper($value);
});

it('passes the context throughout all the ETL steps', function () {
    $items = [];

    // Given
    $etl = (new EtlExecutor())
        ->loadInto(function (string $item) use (&$items) {
            $items[] = $item;
        })
        ->onFlush(fn (FlushEvent $event) => $event->state->context['bar'] = 'baz'); // @phpstan-ignore-line

    // When
    $report = $etl->process(['banana', 'apple'], context: ['foo' => 'bar']);

    // Then
    expect($items)->toBe(['banana', 'apple'])
        ->and($report->context['foo'])->toBe('bar')
        ->and($report->context['bar'])->toBe('baz');
});

it('loads conditionally', function () {
    // Background
    $loader = new class() implements ConditionalLoaderInterface {
        public function supports(mixed $item, EtlState $state): bool
        {
            return 'foo' !== $item;
        }

        public function load(mixed $item, EtlState $state): void
        {
            $state->context[__CLASS__][] = $item;
        }

        public function flush(bool $isPartial, EtlState $state): mixed
        {
            foreach ($state->context[__CLASS__] as $item) {
                $state->context['storage'][] = $item;
            }

            return $state->context['storage'];
        }
    };

    // Given
    $input = ['foo', 'bar', 'baz'];
    $executor = new EtlExecutor(loader: $loader);

    // When
    $report = $executor->process($input, context: ['storage' => []]);

    // Then
    expect($report->output)->toBe(['bar', 'baz']);
});

it('yells if it cannot process extracted data', function () {
    // Given
    $executor = (new EtlExecutor())->withProcessor(
        new class() implements ExtractorProcessorInterface {
            public function supports(mixed $extracted): bool
            {
                return false;
            }

            public function process(EtlExecutor $executor, EtlState $state, mixed $extracted): EtlState
            {
                throw new ShouldNotHappen(new LogicException());
            }
        },
    );

    // When
    $executor->process([]);
})->throws(ExtractException::class);
