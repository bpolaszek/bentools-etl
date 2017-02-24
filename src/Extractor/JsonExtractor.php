<?php

namespace BenTools\ETL\Extractor;

class JsonExtractor implements \IteratorAggregate {

    /**
     * @var \ArrayIterator
     */
    private $json;

    /**
     * JsonExtractor constructor.
     * @param $json
     * @param string|null $path
     * @param string|null $columnIdentifier
     */
    public function __construct($json) {
        if ($json instanceof \ArrayIterator) {
            $this->json = $json;
        }
        elseif (is_string($json)) {
            $this->json = new \ArrayIterator(json_decode($json, true));
        }
        elseif ($json instanceof \stdClass || is_array($json)) {
            $this->json = new \ArrayIterator((array) $json);
        }
        else {
            throw new \InvalidArgumentException("Invalid json");
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator() {
        return $this->json;
    }

}