<?php

namespace BenTools\ETL\Iterator;

use IteratorAggregate;

final class KeysAwareCsvIterator implements IteratorAggregate, CsvIteratorInterface
{
    /**
     * @var CsvIteratorInterface
     */
    private $csvIterator;

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
     * @param CsvIteratorInterface $csvIterator
     * @param array $keys
     * @param bool $skipFirstRow
     */
    public function __construct(CsvIteratorInterface $csvIterator, array $keys = [], bool $skipFirstRow = true)
    {
        $this->csvIterator  = $csvIterator;
        $this->keys         = $keys;
        $this->skipFirstRow = $skipFirstRow;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        foreach ($this->csvIterator as $value) {
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
