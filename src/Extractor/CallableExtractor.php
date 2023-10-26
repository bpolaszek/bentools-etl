<?php

declare(strict_types=1);

namespace Bentools\ETL\Extractor;

use Bentools\ETL\EtlState;
use Closure;

final readonly class CallableExtractor implements ExtractorInterface
{
    public function __construct(
        public Closure $closure,
    ) {
    }

    public function extract(EtlState $state): iterable
    {
        return ($this->closure)($state);
    }
}
