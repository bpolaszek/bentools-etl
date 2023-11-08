<?php

declare(strict_types=1);

namespace BenTools\ETL\Transformer;

use BenTools\ETL\EtlState;

interface TransformerInterface
{
    public function transform(mixed $item, EtlState $state): mixed;
}
