<?php

declare(strict_types=1);

namespace Bentools\ETL\Extractor;

use Bentools\ETL\EtlState;

interface ExtractorInterface
{
    /**
     * @return iterable<mixed>
     */
    public function extract(EtlState $state): iterable;
}
