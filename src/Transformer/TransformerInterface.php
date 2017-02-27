<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Context\ContextElementInterface;

interface TransformerInterface {

    /**
     * Transforms data and hydrates element (should call $element->setTransformedData())
     * @param ContextElementInterface $element
     */
    public function __invoke(ContextElementInterface $element): void;
}