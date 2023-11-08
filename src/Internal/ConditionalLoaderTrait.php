<?php

declare(strict_types=1);

namespace Bentools\ETL\Internal;

use Bentools\ETL\EtlState;
use Bentools\ETL\Loader\ConditionalLoaderInterface;
use Bentools\ETL\Loader\LoaderInterface;

trait ConditionalLoaderTrait
{
    private static function shouldLoad(LoaderInterface $loader, mixed $item, EtlState $state): bool
    {
        if (!$loader instanceof ConditionalLoaderInterface) {
            return true;
        }

        return $loader->supports($item, $state);
    }
}
