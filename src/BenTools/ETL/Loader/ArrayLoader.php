<?php
namespace BenTools\ETL\Loader;

use BenTools\ETL\Interfaces\ContextInterface;
use BenTools\ETL\Interfaces\LoaderInterface;

class ArrayLoader implements LoaderInterface {

    protected $array = [];
    protected $key;

    public function __construct(array &$array = [], callable $key = null) {
        $this->array = &$array;
        $this->key = $key;
    }

    /**
     * loads data into some other persistence service
     *
     * @param mixed            $entity    the data to load
     * @param ContextInterface $context the shared context for current iteration / row / whatever
     *
     * @return mixed
     */
    public function load($entity, ContextInterface $context) {
        if (is_callable($this->key)) {
            $key = call_user_func($this->key, $entity);
            if (array_key_exists($key, $this->array))
                $this->array[$key] = array_merge($this->array[$key], $entity);
            else
                $this->array[$key] = $entity;
        }
        else
            $this->array[] = $entity;
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
    public function getKey() {
        return $this->key;
    }

    /**
     * @param callable $key
     * @return $this - Provides Fluent Interface
     */
    public function setKey(callable $key) {
        $this->key = $key;
        return $this;
    }


}