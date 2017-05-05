<?php

namespace BenTools\ETL\Iterator;

use IteratorAggregate;

class TextLinesIterator implements IteratorAggregate, StringIteratorInterface
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var bool
     */
    private $skipEmptyLines;

    /**
     * TextIterator constructor.
     * @param string $content
     * @param bool $skipEmptyLines
     */
    public function __construct(string $content, bool $skipEmptyLines = true)
    {
        $this->content = $content;
        $this->skipEmptyLines = $skipEmptyLines;
    }

    /**
     * @return string[]
     */
    public function getIterator()
    {
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $this->content);
        foreach ($lines as $line) {
            if (true === $this->skipEmptyLines && 0 === mb_strlen($line)) {
                continue;
            }
            yield $line;
        }
    }
}
