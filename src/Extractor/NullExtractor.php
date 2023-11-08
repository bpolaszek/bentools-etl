<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;

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
