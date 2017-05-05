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
        if (true === $this->skipEmptyLines) {
            return $this->traverseWithStrTok();
        }
        else {
            return $this->traverseWithPregSplit();
        }
    }

    /**
     * Uses a regex to split lines.
     * @return \Generator|string[]
     */
    private function traverseWithPregSplit()
    {
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $this->content);
        foreach ($lines as $line) {
            yield $line;
        }
    }

    /**
     * Uses strtok to split lines. Provides better performance, but skips empty lines.
     * @return \Generator|string[]
     */
    private function traverseWithStrTok()
    {
        $tok = strtok($this->content, "\r\n");
        while (false !== $tok) {
            $line = $tok;
            $tok = strtok("\n\r");
            yield $line;
        }
    }
}
