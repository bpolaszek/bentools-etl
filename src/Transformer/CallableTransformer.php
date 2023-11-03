<?php

declare(strict_types=1);

namespace Bentools\ETL\Transformer;

use Bentools\ETL\EtlState;
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
