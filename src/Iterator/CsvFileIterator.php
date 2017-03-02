<?php

namespace BenTools\ETL\Iterator;

use SplFileObject;

class CsvFileIterator implements \Iterator, \Countable
{

    private $nbLines;
    private $file;
    private $cursor = 0;

    /**
     * CsvFileIterator constructor.
     *
     * @param $filename
     * @param string   $delimiter
     * @param string   $enclosure
     */
    public function __construct(SplFileObject $file, $delimiter = ',', $enclosure = '"', $escapeString = '\\')
    {
        $this->file = $file;
        $this->file->setCsvControl($delimiter, $enclosure, $escapeString);
        $this->file->setFlags(SplFileObject::READ_CSV);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->cursor = 0;
        $this->file->rewind();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->file->current();
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->file->key();
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->cursor++;
        $this->file->next();
        if ($this->valid()) {
            $this->cursor++;
        }
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        // Avoid blank lines
        if (true === $this->file->valid()) {
            $current = $this->file->current();
            if (!is_array($current)) {
                throw new \RuntimeException("The current iteration is a string, are you sure you use the correct delimiter?");
            }
            return !empty(
                array_filter(
                    $current,
                    function ($cell) {
                        return null !== $cell;
                    }
                )
            );
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        if (null === $this->nbLines) {
            // Store flags and position
            $flags   = $this->file->getFlags();
            $current = $this->file->key();

            // Prepare count by resetting flags as READ_CSV for example make the trick very slow
            $this->file->setFlags(null);

            // Go to the larger INT we can as seek will not throw exception, errors, notice if we go beyond the bottom line
            $this->file->seek(PHP_INT_MAX);

            // We store the key position
            // As key starts at 0, we add 1
            $this->nbLines = $this->file->key() + 1;

            // We move to old position
            // As seek method is longer with line number < to the max line number, it is better to count at the beginning of iteration
            $this->file->seek($current);

            // Re set flags
            $this->file->setFlags($flags);
        }

        return $this->nbLines;
    }

    /**
     * @inheritdoc
     */
    public function seek($position)
    {
        $this->file->seek($position);
    }

    /**
     * @return int
     */
    public function getCursor()
    {
        return $this->cursor;
    }
}
