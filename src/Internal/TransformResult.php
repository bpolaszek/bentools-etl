<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use Generator;
use IteratorAggregate;
use Traversable;

/**
 * @internal
 *
 * @implements IteratorAggregate<mixed>
 */
final class TransformResult implements IteratorAggregate
{
    public mixed $value;
    public bool $iterable;

    private function __construct()
    {
    }

    public function getIterator(): Traversable
    {
        if ($this->iterable) {
            yield from $this->value;
        } else {
            yield $this->value;
        }
    }

    public static function create(mixed $value): self
    {
        static $prototype;
        $prototype ??= new self();

        if ($value instanceof self) {
            return $value;
        }

        $that = clone $prototype;
        if ($value instanceof Generator) {
            $that->value = [...$value];
            $that->iterable = true;
        } else {
            $that->value = $value;
            $that->iterable = false;
        }

        return $that;
    }
}
