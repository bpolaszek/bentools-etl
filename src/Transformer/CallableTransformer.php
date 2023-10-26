<?php

declare(strict_types=1);

namespace Bentools\ETL\Transformer;

use Bentools\ETL\EtlState;
use Closure;
use Generator;

final readonly class CallableTransformer implements TransformerInterface
{
    public function __construct(
        public Closure $closure,
    ) {
    }

    /**
     * @return Generator<mixed>
     */
    public function transform(mixed $item, EtlState $state): Generator
    {
        return ($this->closure)($item, $state);
    }
}
