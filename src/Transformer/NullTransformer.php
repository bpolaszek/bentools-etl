<?php

declare(strict_types=1);

namespace Bentools\ETL\Transformer;

use Bentools\ETL\EtlState;
use Generator;

final readonly class NullTransformer implements TransformerInterface
{
    public function transform(mixed $item, EtlState $state): Generator
    {
        yield $item;
    }
}
