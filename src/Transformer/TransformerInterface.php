<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Etl;

interface TransformerInterface
{

    /**
     * Transform $value.
     *
     * @param     $value
     * @param     $key
     * @param Etl $etl
     * @return \Generator - yield values to load
     */
    public function transform($value, $key, Etl $etl): \Generator;
}
