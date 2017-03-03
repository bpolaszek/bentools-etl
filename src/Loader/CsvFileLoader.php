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
     * @var array
     */
    private $keys;

    /**
     * @var bool
     */
    private $startedWriting = false;

    /**
     * @inheritDoc
     */
    public function __construct(
        \SplFileObject $file,
        LoggerInterface $logger = null,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\',
        array $keys = []
    ) {
        parent::__construct($file, $logger);
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape    = $escape;
        $this->keys = $keys;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @param array $keys
     * @return $this - Provides Fluent Interface
     */
    public function setKeys(array $keys)
    {
        if (true === $this->startedWriting) {
            throw new \RuntimeException("It is too late to set the keys, the loader has already started writing.");
        }

        $this->keys = $keys;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContextElementInterface $element): void
    {
        if (!empty($this->keys) && false === $this->startedWriting) {
            if (false !== (bool) $this->file->fputcsv($this->keys, $this->delimiter, $this->enclosure, $this->escape)) {
                $this->startedWriting = true;
            }
        }

        $bytes = $this->file->fputcsv($element->getData(), $this->delimiter, $this->enclosure, $this->escape);

        if (0 !== $bytes && false === $this->startedWriting) {
            $this->startedWriting = true;
        }

        $this->logger->debug(
            'Write a field array as a CSV line',
            [
            'id' => $element->getId(),
            'data' => $element->getData(),
            'filename' => $this->file->getBasename(),
            'bytes' => $bytes
            ]
        );
    }
}
