<?php
namespace BenTools\ETL\Loader;

use BenTools\ETL\Interfaces\ContextInterface;
use BenTools\ETL\Interfaces\LoaderInterface;
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
     * @param mixed $data
     * @param ContextInterface $context
     */
    public function load($data, ContextInterface $context) {
        if (is_array($data) && $data && array_values($data)[0])
            $this->data[]   =   $data;
        else
            $context->shouldBreak(true);
    }

    /**
     * @param ContextInterface $context
     * @throws FileException
     */
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
     * @param ContextInterface $context
     */
    public function clear(ContextInterface $context) {

    }

}