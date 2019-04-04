<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;
use SplFileObject;

final class FileLoader implements LoaderInterface
{

    /**
     * @var SplFileObject
     */
    protected $file;

    /**
     * FileLoader constructor.
     *
     * @param SplFileObject  $file
     */
    public function __construct(SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * @inheritDoc
     */
    public function load(\Generator $items, $key, Etl $etl): void
    {
        foreach ($items as $item) {
            $this->file->fwrite($item);
        }
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function commit(bool $partial): void
    {
    }
}
