<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;

interface ExtractorProcessorInterface
{
    public function supports(mixed $extracted): bool;

    /**
     * @param Ref<EtlState> $stateHolder
     */
    public function process(EtlExecutor $executor, Ref $stateHolder, mixed $extracted): EtlState;
}
