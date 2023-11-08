<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader;

use ArrayObject;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EtlState;
use Bentools\ETL\Loader\ChainLoader;
use Bentools\ETL\Loader\ConditionalLoaderInterface;

use function expect;

it('chains loaders', function () {
    // Background
    $a = new ArrayObject();
    $b = new ArrayObject();
    $c = new ArrayObject();
    $loader = (new ChainLoader(
        fn (string $item) => $a[] = $item, // @phpstan-ignore-line
        fn (string $item) => $b[] = $item, // @phpstan-ignore-line
    ))
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
        );

    // Given
    $input = ['foo', 'bar'];
    $executor = new EtlExecutor(loader: $loader);

    // When
    $executor->process($input, context: ['storage' => $c]);

    // Then
    expect([...$a])->toBe(['foo', 'bar'])
        ->and([...$b])->toBe(['foo', 'bar'])
        ->and([...$c])->toBe(['bar']);
});
