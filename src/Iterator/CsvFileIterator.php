<?php

namespace BenTools\ETL\Iterator;

use FilterIterator;
use SplFileObject;

class CsvFileIterator extends FilterIterator implements \Countable
{

    private $nbLines;
    private $file;

    /**
     * CsvFileIterator constructor.
     *
     * @param $filename
     * @param string $delimiter
     * @param string $enclosure
     */
    public function __construct(SplFileObject $file, $delimiter = ',', $enclosure = '"', $escapeString = '\\')
    {
        $this->file = $file;
        $this->file->setCsvControl($delimiter, $enclosure, $escapeString);
        $this->file->setFlags(SplFileObject::READ_CSV);
        parent::__construct($this->file);
    }

    /**
     * @inheritDoc
     */
    public function accept()
    {
        $current = $this->getInnerIterator()->current();
        return !empty(array_filter(
            $current,
            function ($cell) {
                return null !== $cell;
            }
        ));
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
}
