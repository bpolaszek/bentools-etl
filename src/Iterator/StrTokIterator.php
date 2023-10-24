<?php

declare(strict_types=1);

namespace Bentools\ETL\Iterator;

use IteratorAggregate;
use Traversable;

use function rtrim;
use function strtok;

use const PHP_EOL;

/**
 * @internal
 *
 * @implements IteratorAggregate<string>
 */
final readonly class StrTokIterator implements IteratorAggregate
{
    public function __construct(
        public string $content,
    ) {
    }

    public function getIterator(): Traversable
    {
        $tok = strtok($this->content, "\r\n");
        while (false !== $tok) {
            $line = $tok;
            $tok = strtok("\n\r");
            yield rtrim($line, PHP_EOL);
        }
    }
}
