<?php

namespace BenTools\ETL\Extractor;

class CsvExtractor implements \Iterator, \Countable {

    private $nbLines;
    private $csv;

    /**
     * CsvExtractor constructor.
     * @param $filename
     * @param string $delimiter
     * @param string $enclosure
     */
    public function __construct($filename, $delimiter = ';', $enclosure = '"') {
        $this->csv = new \SplFileObject($filename);
        $this->csv->setFlags(\SplFileObject::READ_CSV);
        $this->csv->setCsvControl($delimiter, $enclosure);
    }

    /**
     * @inheritdoc
     */
    public function rewind() {
        $this->csv->rewind();
    }

    /**
     * @inheritdoc
     */
    public function current() {
        return $this->csv->current();
    }

    /**
     * @inheritdoc
     */
    public function key() {
        return $this->csv->key();
    }

    /**
     * @inheritdoc
     */
    public function next() {
        $this->csv->next();
    }

    /**
     * @inheritdoc
     */
    public function valid() {
        return $this->csv->valid();
    }

    /**
     * @inheritdoc
     */
    public function count() {
        if (null === $this->nbLines) {

            // Store flags and position
            $flags   = $this->csv->getFlags();
            $current = $this->csv->key();

            // Prepare count by resetting flags as READ_CSV for example make the trick very slow
            $this->csv->setFlags(null);

            // Go to the larger INT we can as seek will not throw exception, errors, notice if we go beyond the bottom line
            $this->csv->seek(PHP_INT_MAX);

            // We store the key position
            // As key starts at 0, we add 1
            $this->nbLines = $this->csv->key() + 1;

            // We move to old position
            // As seek method is longer with line number < to the max line number, it is better to count at the beginning of iteration
            $this->csv->seek($current);

            // Re set flags
            $this->csv->setFlags($flags);
        }

        return $this->nbLines;
    }

    /**
     * @inheritdoc
     */
    public function seek($position) {
        $this->csv->seek($position);
    }
}