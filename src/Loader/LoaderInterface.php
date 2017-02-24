<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Context\ContextElementInterface;

interface LoaderInterface {

    /**
     * Loads the element
     * @param ContextElementInterface $element
     * @return mixed
     */
    public function __invoke(ContextElementInterface $element): void;

}