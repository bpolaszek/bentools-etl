<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;

final class ArrayLoader implements LoaderInterface
{

    /**
     * @var array
     */
    protected $array;

    /**
     * @var bool
     */
    private $preserveKeys;

    /**
     * ArrayLoader constructor.
     *
     * @param array $array
     */
    public function __construct(bool $preserveKeys = true, array &$array = [])
    {
        $this->array = &$array;
        $this->preserveKeys = $preserveKeys;
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
    public function load(\Generator $items, $key, Etl $etl): void
    {
        foreach ($items as $v) {
            if ($this->preserveKeys) {
                $this->array[$key] = $v;
            } else {
                $this->array[] = $v;
            }
        }
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

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }
}
