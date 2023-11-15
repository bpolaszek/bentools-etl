<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;

interface ExtractorProcessorInterface
{
    public function supports(mixed $extracted): bool;

    public function process(EtlExecutor $executor, EtlState $state, mixed $extracted): EtlState;
}
