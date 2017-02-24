<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Context\ContextElementInterface;

interface TransformerInterface {

    /**
     * Retrieves extracted data from the context
     * Transforms data
     * Hydrates context with transformed data
     * @param ContextElementInterface $element
     */
    public function __invoke(ContextElementInterface $element): void;
}