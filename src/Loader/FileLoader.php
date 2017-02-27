<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Context\ContextElementInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class FileLoader implements LoaderInterface, LoggerAwareInterface {

    use LoggerAwareTrait;

    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * FileLoader constructor.
     * @param \SplFileObject $file
     * @param LoggerInterface $logger
     */
    public function __construct(\SplFileObject $file, LoggerInterface $logger = null) {
        $this->file = $file;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContextElementInterface $element): void {
        $bytes = $this->file->fwrite($element->getTransformedData());
        $this->logger->debug('Write to file', [
            'id' => $element->getId(),
            'data' => $element->getTransformedData(),
            'filename' => $this->file->getBasename(),
            'bytes' => $bytes
        ]);
    }

}