<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlState;
use BenTools\ETL\Loader\ConditionalLoaderInterface;
use BenTools\ETL\Loader\LoaderInterface;

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
