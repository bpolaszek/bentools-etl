<?php

declare(strict_types=1);

namespace BenTools\ETL\Loader;

use BenTools\ETL\EtlState;

interface ConditionalLoaderInterface extends LoaderInterface
{
    public function supports(mixed $item, EtlState $state): bool;
}
