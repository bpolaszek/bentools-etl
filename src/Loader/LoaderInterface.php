<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;

/**
 * A loader is responsible to load normalized data into the appropriate target.
 */
interface LoaderInterface
{

    /**
     * Init loader (start a transaction, if supported) and reset loader state.
     * Optionnal arguments can be passed.
     */
    public function init(): void;

    /**
     * Load elements.
     *
     * @param \Generator $items
     * @param            $key
     * @param Etl        $etl
     */
    public function load(\Generator $items, $key, Etl $etl): void;

    /**
     * Flush elements (if supported).
     *
     * @param bool $partial - whether or not there remains elements to process.
     */
    public function commit(bool $partial): void;

    /**
     * Rollback (if supported).
     */
    public function rollback(): void;
}
