<?php
namespace BenTools\ETL\Loader;

use BenTools\ETL\Interfaces\ContextInterface;
use BenTools\ETL\Interfaces\LoaderInterface;

class ArrayLoader implements LoaderInterface {

    protected $array = [];
    protected $keyFn;

    public function __construct(array &$array = [], callable $keyFn = null) {
        $this->array = &$array;
        $this->keyFn = $keyFn;
    }

    /**
     * loads data into some other persistence service
     *
     * @param mixed            $entity    the data to load
     * @param ContextInterface $context the shared context for current iteration / row / whatever
     *
     * @return mixed
     */
    public function load($data, ContextInterface $context) {
        if (is_callable($this->keyFn)) {
            $key = call_user_func($this->keyFn, $data);
            if (array_key_exists($key, $this->array))
                $this->array[$key] = array_merge($this->array[$key], $data);
            else
                $this->array[$key] = $data;
        }
        else {
            $this->array[] = $data;
        }
    }

    /**
     * Flush the loader
     *
     * @param ContextInterface $context the shared context for current iteration / row / whatever
     **/
    public function flush(ContextInterface $context) {

    }

    /**
     * Reset the loader
     *
     * @param ContextInterface $context the shared context for current iteration / row / whatever
     **/
    public function clear(ContextInterface $context) {
        $this->array = [];
    }

    /**
     * @return array
     */
    public function getArray() {
        return $this->array;
    }

    /**
     * @param array $array
     * @return $this - Provides Fluent Interface
     */
    public function setArray($array) {
        $this->array = $array;
        return $this;
    }

    /**
     * @return callable
     */
    public function getKeyFn() {
        return $this->keyFn;
    }

    /**
     * @param callable $keyFn
     * @return $this - Provides Fluent Interface
     */
    public function setKeyFn(callable $keyFn) {
        $this->keyFn = $keyFn;
        return $this;
    }


}