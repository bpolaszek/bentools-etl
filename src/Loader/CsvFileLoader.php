<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;
use SplFileObject;

final class CsvFileLoader implements LoaderInterface
{
    private $file;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure;

    /**
     * @var string
     */
    private $escape;

    /**
     * @var array
     */
    private $keys;

    /**
     * @inheritDoc
     */
    public function __construct(
        SplFileObject $file,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\',
        array $keys = []
    ) {
        $this->file = $file;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        $this->keys = $keys;
    }

    /**
     * @inheritDoc
     */
    public function load(\Generator $generator, $key, Etl $etl): void
    {
        foreach ($generator as $row) {
            $this->file->fputcsv($row, $this->delimiter, $this->enclosure, $this->escape);
        }
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        if (!empty($this->keys)) {
            $this->file->fputcsv($this->keys, $this->delimiter, $this->enclosure, $this->escape);
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
