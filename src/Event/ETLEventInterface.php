<?php

namespace BenTools\ETL\Event;

use BenTools\ETL\Context\ContextElementInterface;

interface ETLEventInterface {

    /**
     * @return ContextElementInterface
     */
    public function getElement(): ?ContextElementInterface;
}