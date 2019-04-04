<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Etl;

interface ExtractorInterface
{

    /**
     * @param mixed $input
     * @param Etl   $etl
     * @return iterable
     */
    public function extract($input, Etl $etl): iterable;
}
