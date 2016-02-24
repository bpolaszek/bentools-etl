<?php
/**
 * LoaderInterface.php
 * Generated by PhpStorm - 02/2016
 * Project bentools-etl
 * @author Beno!t POLASZEK
 **/

namespace BenTools\ETL\Interfaces;

interface LoaderInterface {

    /**
     * Loads data into some other persistence service
     *
     * @param mixed $data the data to load
     * @param ContextInterface $context the shared context for current iteration / row / whatever
     *
     * @return mixed
     */
    public function load($data, ContextInterface $context);

    /**
     * Flush the loader
     *
     * @param ContextInterface $context the shared context for current iteration / row / whatever
     **/
    public function flush(ContextInterface $context);

    /**
     * Reset the loader
     *
     * @param ContextInterface $context the shared context for current iteration / row / whatever
     **/
    public function clear(ContextInterface $context);
}