<?php

namespace BenTools\ETL\Iterator;

/**
 * Extracts lines of a file with EOL trimming.
 */
final class FileLinesIterator implements \IteratorAggregate
{
    /**
     * @var \SplFileObject
     */
    private $file;

    /**
     * FileLinesIterator constructor.
     */
    public function __construct(\SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        foreach ($this->file as $row) {
            yield rtrim($row, \PHP_EOL);
        }
    }

    /**
     * @param string $fileName
     * @return FileLinesIterator
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function createFromFilename(string $fileName): self
    {
        return new self(new \SplFileObject($fileName));
    }
}
