<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Etl;
use BenTools\ETL\Exception\UnexpectedTypeException;
use BenTools\ETL\Iterator\CsvFileIterator;
use BenTools\ETL\Iterator\CsvStringIterator;
use BenTools\ETL\Iterator\KeysAwareCsvIterator;

final class CsvExtractor implements ExtractorInterface
{
    const EXTRACT_AUTO = 1;
    const EXTRACT_FROM_STRING = 1;
    const EXTRACT_FROM_FILE = 2;

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
    private $escapeString;

    /**
     * @var bool
     */
    private $createKeys;

    /**
     * @var int
     */
    private $type;

    /**
     * CsvExtractor constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->delimiter = $options['delimiter'] ?? ',';
        $this->enclosure = $options['enclosure'] ?? '"';
        $this->escapeString = $options['escape_string'] ?? '\\';
        $this->createKeys = $options['create_keys'] ?? false;
        $this->type = $options['type'] ?? self::EXTRACT_AUTO;
    }

    /**
     * @inheritDoc
     */
    public function extract($input, Etl $etl): iterable
    {
        switch ($this->type) {
            case self::EXTRACT_FROM_STRING:
                $iterator = $this->extractFromString($input);
                break;
            case self::EXTRACT_FROM_FILE:
                $iterator = $this->extractFromFile($input);
                break;
            case self::EXTRACT_AUTO:
                $iterator = $this->extractAuto($input);
                break;
            default:
                throw new \InvalidArgumentException('Invalid input.');
        }

        return true === $this->createKeys ? new KeysAwareCsvIterator($iterator) : $iterator;
    }

    /**
     * @param $string
     * @return CsvStringIterator
     */
    private function extractFromString($string)
    {
        return CsvStringIterator::createFromText($string, $this->delimiter, $this->enclosure, $this->escapeString);
    }

    /**
     * @param $file
     * @return CsvFileIterator
     * @throws UnexpectedTypeException
     */
    private function extractFromFile($file)
    {
        if ($file instanceof \SplFileInfo) {
            return new CsvFileIterator($file, $this->delimiter, $this->enclosure, $this->escapeString);
        };

        UnexpectedTypeException::throwIfNot($file, 'string');

        return CsvFileIterator::createFromFilename($file, $this->delimiter, $this->enclosure, $this->escapeString);
    }

    /**
     * @param $input
     * @return CsvFileIterator|CsvStringIterator
     * @throws UnexpectedTypeException
     */
    private function extractAuto($input)
    {
        if (\strlen($input) < 3000 && \file_exists($input)) {
            return $this->extractFromFile($input);
        }

        return $this->extractFromString($input);
    }
}
