<?php
namespace BenTools\ETL\Interfaces;

interface ExtractorInterface {

    /**
     * Extracts data to be transformed
     *
     * @param ContextInterface $context
     * @return array
     */
    public function extract(ContextInterface $context);
}