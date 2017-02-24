<?php

namespace BenTools\ETL\Loader;

interface FlushableLoaderInterface extends LoaderInterface {

    /**
     * Flushes the loader
     */
    public function flush(): void;

}