<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;

interface ExtractorInterface
{
    /**
     * @return iterable<mixed>
     */
    public function extract(EtlState $state): iterable;
}
