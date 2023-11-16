<?php

declare(strict_types=1);

namespace BenTools\ETL\Processor;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;

interface ProcessorInterface
{
    public function supports(mixed $extracted): bool;

    public function process(EtlExecutor $executor, EtlState $state, mixed $extracted): EtlState;
}
