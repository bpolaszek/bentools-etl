<?php

declare(strict_types=1);

namespace BenTools\ETL\Transformer;

use BenTools\ETL\EtlState;
use Closure;
use Generator;

final readonly class CallableBatchTransformer implements BatchTransformerInterface
{
    public Closure $closure;

    public function __construct(
        callable $callback,
    ) {
        $this->closure = $callback(...);
    }

    public function transform(array $items, EtlState $state): Generator
    {
        yield from ($this->closure)($items, $state);
    }
}
