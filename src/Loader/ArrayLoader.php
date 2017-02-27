<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Context\ContextElementInterface;

class ArrayLoader implements LoaderInterface {

    /**
     * @var array
     */
    protected $array;

    /**
     * ArrayLoader constructor.
     * @param array $array
     */
    public function __construct(array &$array = []) {
        $this->array = &$array;
    }

    /**
     * @return array
     */
    public function getArray() {
        return $this->array;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContextElementInterface $element): void {
        $this->array[$element->getId()] = $element->getTransformedData();
    }
}