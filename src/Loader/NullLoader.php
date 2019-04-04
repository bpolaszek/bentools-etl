<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;

final class NullLoader implements LoaderInterface
{

    /**
     * @inheritDoc
     */
    public function init(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function load(\Generator $items, $key, Etl $etl): void
    {
        foreach ($items as $item) {
            continue;
        }
    }

    /**
     * @inheritDoc
     */
    public function commit(bool $partial): void
    {
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
    }
}
