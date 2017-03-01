<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Context\ContextElementInterface;

interface ExtractorInterface
{

    /**
     * Creates a context element.
     *
     * @param  $key
     * @param  $value
     * @return ContextElementInterface
     */
    public function __invoke($key, $value): ContextElementInterface;
}
