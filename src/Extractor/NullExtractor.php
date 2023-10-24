<?php

declare(strict_types=1);

namespace Bentools\ETL\Extractor;

use Bentools\ETL\EtlState;

/**
 * @codeCoverageIgnore
 */
final readonly class NullExtractor implements ExtractorInterface
{
    public function extract(EtlState $state): iterable
    {
        yield from [];
    }
}
