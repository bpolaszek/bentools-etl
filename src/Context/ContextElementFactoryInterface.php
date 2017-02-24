<?php

namespace BenTools\ETL\Context;

interface ContextElementFactoryInterface {

    /**
     * Creates an element.
     * @param $identifier
     * @param $extractedData
     * @return ContextElementInterface
     */
    public function createContext($identifier, $extractedData): ContextElementInterface;

}