<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader;

use ArrayObject;
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\Loader\CallableLoader;
use BenTools\ETL\Loader\ConditionalLoaderInterface;

use function BenTools\ETL\chain;
use function expect;

it('chains loaders', function () {
    // Background
    $a = new ArrayObject();
    $b = new ArrayObject();
    $c = new ArrayObject();

    $executor = new EtlExecutor(loader: new CallableLoader(
        fn (string $item) => $a[] = $item, // @phpstan-ignore-line
    ));
    $executor = $executor->loadInto(
        chain($executor->loader)
            ->with(fn (string $item) => $b[] = $item) // @phpstan-ignore-line
            ->with(
                new class() implements ConditionalLoaderInterface {
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
                },
            )
    );

    // Given
    $input = ['foo', 'bar'];

    // When
    $executor->process($input, context: ['storage' => $c]);

    // Then
    expect([...$a])->toBe(['foo', 'bar'])
        ->and([...$b])->toBe(['foo', 'bar'])
        ->and([...$c])->toBe(['bar']);
});

it('silently chains loaders', function () {
    // Background
    $a = new ArrayObject();
    $b = new ArrayObject();

    // Given
    $input = ['foo', 'bar'];
    $executor = (new EtlExecutor())->loadInto(
        fn (string $item) => $a[] = $item, // @phpstan-ignore-line
        fn (string $item) => $b[] = $item, // @phpstan-ignore-line
    );

    // When
    $executor->process($input);

    // Then
    expect([...$a])->toBe(['foo', 'bar'])
        ->and([...$b])->toBe(['foo', 'bar']);
});
