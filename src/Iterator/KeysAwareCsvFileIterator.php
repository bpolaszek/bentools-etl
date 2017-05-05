<?php

namespace BenTools\ETL\Iterator;

use IteratorAggregate;

class KeysAwareCsvFileIterator implements IteratorAggregate
{
    /**
     * @var CsvFileIterator
     */
    private $csvFileIterator;

    /**
     * @var array
     */
    private $keys = [];

    /**
     * @var bool
     */
    private $skipFirstRow;

    /**
     * @var bool
     */
    private $started = false;

    /**
     * KeysAwareCsvFileIterator constructor.
     * @param CsvFileIterator $csvFileIterator
     * @param array $keys
     * @param bool $skipFirstRow
     */
    public function __construct(CsvFileIterator $csvFileIterator, array $keys = [], bool $skipFirstRow = true)
    {
        $this->csvFileIterator = $csvFileIterator;
        $this->keys = $keys;
        $this->skipFirstRow = $skipFirstRow;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        foreach ($this->csvFileIterator as $value) {
            if (false === $this->started) {
                $this->started = true;
                if (empty($this->keys)) {
                    $this->keys = $value;
                }
                if (true === $this->skipFirstRow) {
                    continue;
                }
            }
            yield array_combine($this->keys, $value);
        }
    }
}
