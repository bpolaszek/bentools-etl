<?php

declare(strict_types=1);

namespace BenTools\ETL\Iterator;

use Iterator;
use OutOfRangeException;

use function BenTools\ETL\iterable_to_iterator;

/**
 * @internal
 *
 * @template T
 */
final class ConsumableIterator
{
    private readonly Iterator $iterator;
    private bool $started = false;
    private bool $ended = false;

    /**
     * @param iterable<T> $items
     */
    public function __construct(iterable $items)
    {
        $this->iterator = iterable_to_iterator($items);
    }

    public function consume(): mixed
    {
        if ($this->ended) {
            throw new OutOfRangeException('This iterator has no more items.'); // @codeCoverageIgnore
        }

        if (!$this->started) {
            $this->iterator->rewind();
            $this->started = true;
        }

        $value = $this->iterator->current();
        $this->iterator->next();

        if (!$this->iterator->valid()) {
            $this->ended = true;
        }

        return $value;
    }

    public function isComplete(): bool
    {
        return $this->ended;
    }
}
