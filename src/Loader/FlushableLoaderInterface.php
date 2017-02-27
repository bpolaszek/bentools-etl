<?php

namespace BenTools\ETL\Loader;

interface FlushableLoaderInterface extends LoaderInterface {

    /**
     * Returns wether or not the loader should flush after the last load.
     * @return bool
     */
    public function shouldFlushAfterLoad(): bool;

    /**
     * Flushes the loader.
     */
    public function flush(): void;

}