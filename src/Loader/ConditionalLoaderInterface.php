<?php

declare(strict_types=1);

namespace Bentools\ETL\Loader;

use Bentools\ETL\EtlState;

interface ConditionalLoaderInterface extends LoaderInterface
{
    public function supports(mixed $item, EtlState $state): bool;
}
