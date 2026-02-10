<?php

declare(strict_types=1);

namespace BenTools\ETL\Transformer;

use BenTools\ETL\EtlState;
use Generator;

interface BatchTransformerInterface
{
    /**
     * Transform a batch of items at once.
     *
     * Each yielded value becomes an individual item for the load phase.
     *
     * @param list<mixed> $items
     */
    public function transform(array $items, EtlState $state): Generator;
}
