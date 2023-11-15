<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlState;

interface ExtractorProcessorInterface
{
    public function supports(mixed $extracted): bool;

    public function process(EtlState $etlState): EtlState;
}
