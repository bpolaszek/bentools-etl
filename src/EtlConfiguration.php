<?php

declare(strict_types=1);

namespace Bentools\ETL;

final readonly class EtlConfiguration
{
    public function __construct(
        public int $flushEvery = 1,
    ) {
    }
}
