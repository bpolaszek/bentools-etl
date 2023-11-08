<?php

declare(strict_types=1);

namespace BenTools\ETL\Iterator;

use IteratorAggregate;
use Traversable;

use function rtrim;
use function Safe\preg_split;

/**
 * @internal
 *
 * @implements IteratorAggregate<string>
 */
final readonly class PregSplitIterator implements IteratorAggregate
{
    public function __construct(
        public string $content,
    ) {
    }

    public function getIterator(): Traversable
    {
        $lines = preg_split("/((\r?\n)|(\r\n?))/", $this->content);
        foreach ($lines as $line) {
            yield rtrim($line, PHP_EOL);
        }
    }
}
