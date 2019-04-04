<?php

namespace BenTools\ETL\Recipe;

use BenTools\ETL\EtlBuilder;

abstract class Recipe
{

    /**
     * @param EtlBuilder $builder
     * @return EtlBuilder
     */
    abstract public function updateBuilder(EtlBuilder $builder): EtlBuilder;
}
