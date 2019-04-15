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
            yield self::combine($this->keys, $value);
        }
    }

    /**
     * Combine keys & values
     *
     * @param array $keys
     * @param array $values
     * @return array
     */
    private static function combine(array $keys, array $values): array
    {
        $nbKeys = \count($keys);
        $nbValues = \count($values);

        if ($nbKeys < $nbValues) {
            return \array_combine($keys, \array_slice(\array_values($values), 0, $nbKeys));
        }

        if ($nbKeys > $nbValues) {
            return \array_combine($keys, \array_merge($values, \array_fill(0, $nbKeys - $nbValues, null)));
        }

        return \array_combine($keys, $values);
    }
}
