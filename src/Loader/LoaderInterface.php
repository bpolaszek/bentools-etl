<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Context\ContextElementInterface;

interface LoaderInterface {

    /**
     * Loads the element into the persistence layer (ORM, file, HTTP, ...)
     * @param ContextElementInterface $element
     */
    public function __invoke(ContextElementInterface $element): void;
}