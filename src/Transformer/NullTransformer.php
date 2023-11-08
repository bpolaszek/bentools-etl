<?php

declare(strict_types=1);

namespace BenTools\ETL\Transformer;

use BenTools\ETL\EtlState;

final readonly class NullTransformer implements TransformerInterface
{
    public function transform(mixed $item, EtlState $state): mixed
    {
        return $item;
    }
}
