<?php

namespace BenTools\ETL\Iterator;

class JsonIterator implements \IteratorAggregate
{

    /**
     * @var \ArrayIterator
     */
    private $json;

    /**
     * JsonIterator constructor.
     *
     * @param mixed $json
     */
    public function __construct($json)
    {
        if ($json instanceof \ArrayIterator) {
            $this->json = $json;
        } elseif (is_string($json)) {
            $this->json = new \ArrayIterator(json_decode($json, true));
        } elseif ($json instanceof \stdClass || is_array($json)) {
            $this->json = new \ArrayIterator((array) $json);
        } else {
            throw new \InvalidArgumentException("Invalid json input");
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return $this->json;
    }
}
