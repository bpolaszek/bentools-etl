<?php

declare(strict_types=1);

namespace Bentools\ETL\Transformer;

use Bentools\ETL\EtlState;

interface TransformerInterface
{
    public function transform(mixed $item, EtlState $state): mixed;
}
