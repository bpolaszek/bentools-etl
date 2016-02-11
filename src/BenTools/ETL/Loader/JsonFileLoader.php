<?php
namespace BenTools\ETL\Loader;

use Knp\ETL\ContextInterface;
use Knp\ETL\LoaderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use BenTools\ETL\Context;

class JsonFileLoader implements LoaderInterface, LoggerAwareInterface {

    use         LoggerAwareTrait;

    protected   $data   =   [];

    protected   $filePath;

    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * loads data into some other persistence service
     *
     * @param mixed            $data    the data to load
     * @param Context $context the shared context for current iteration / row / whatever
     *
     * @return mixed
     */
    public function load($data, ContextInterface $context) {
        if (is_array($data) && $data && array_values($data)[0])
            $this->data[]   =   $data;
        else
            $context->shouldBreak(true);
    }

    /**
     * Flush the loader
     *
     * @param Context $context the shared context for current iteration / row / whatever
     **/
    public function flush(ContextInterface $context) {

        if (!$this->data) {
            $context->getLogger()->notice("Nothing to write.");
        }
        else {
            $jsonOutput = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($jsonOutput === false)
                throw new \RuntimeException(json_last_error_msg());

            $write = file_put_contents($this->filePath, $jsonOutput);
            if ($write === false)
                throw new FileException(sprintf("Unable to write into %s", $this->filePath));
        }

    }

    /**
     * Reset the loader
     *
     * @param ContextInterface $context the shared context for current iteration / row / whatever
     **/
    public function clear(ContextInterface $context) {

    }

}