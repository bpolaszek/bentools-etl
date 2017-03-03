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
            $flags   = $this->file->getFlags();
            $current = $this->file->key();
            $this->file->setFlags(null);
            $this->file->seek(PHP_INT_MAX);
            $this->nbLines = $this->file->key() + 1;
            $this->file->seek($current);
            $this->file->setFlags($flags);
        }

        return $this->nbLines;
    }
}
