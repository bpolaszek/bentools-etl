<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Etl;

/**
 * An extractor is responsible to convert any source of data (file, resource, ...)
 * into an iterable of items.
 */
interface ExtractorInterface
{

    /**
     * @param mixed $input
     * @param Etl   $etl
     * @return iterable
     */
    public function extract($input, Etl $etl): iterable;
}
