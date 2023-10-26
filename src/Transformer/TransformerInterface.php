<?php

declare(strict_types=1);

namespace Bentools\ETL\Transformer;

use Bentools\ETL\EtlState;
use Generator;

interface TransformerInterface
{
    /**
     * @return Generator<mixed>
     */
    public function transform(mixed $item, EtlState $state): Generator;
}
