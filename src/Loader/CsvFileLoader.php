<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Context\ContextElementInterface;
use Psr\Log\LoggerInterface;

class CsvFileLoader extends FileLoader
{

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure;
    /**
     * @var string
     */
    private $escape;

    /**
     * @inheritDoc
     */
    public function __construct(\SplFileObject $file, LoggerInterface $logger = null, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        parent::__construct($file, $logger);
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape    = $escape;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContextElementInterface $element): void
    {
        $bytes = $this->file->fputcsv($element->getTransformedData(), $this->delimiter, $this->enclosure, $this->escape);
        $this->logger->debug(
            'Write a field array as a CSV line',
            [
            'id' => $element->getId(),
            'data' => $element->getTransformedData(),
            'filename' => $this->file->getBasename(),
            'bytes' => $bytes
            ]
        );
    }
}
