<?php

declare(strict_types=1);

namespace BenTools\ETL\Transformer;

use BenTools\ETL\EtlState;
use Closure;

final readonly class CallableTransformer implements TransformerInterface
{
    public function __construct(
        public Closure $closure,
    ) {
    }

    public function transform(mixed $item, EtlState $state): mixed
    {
        return ($this->closure)($item, $state);
    }
}
