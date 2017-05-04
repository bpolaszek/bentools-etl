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
     * @param string   $delimiter
     * @param string   $enclosure
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
        return !empty(
            array_filter(
                $current,
                function ($cell) {
                    return null !== $cell;
                }
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        if (null === $this->nbLines) {
            $this->rewind();
            $this->nbLines = count(iterator_to_array($this));
        }

        return $this->nbLines;
    }
}
