<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Etl;

/**
 * A transformer is responsible to generate transformations / normalizations on extracted data.
 */
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
