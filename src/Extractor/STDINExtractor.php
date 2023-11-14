<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;
use Iterator;
use SplFileObject;

/**
 * @implements Iterator<int, string>
 */
final class STDINExtractor implements Iterator, ExtractorInterface
{
    private SplFileObject $stdIn;

    public function current(): string|false
    {
        return $this->stdIn->current();
    }

    public function next(): void
    {
        $this->stdIn->next();
    }

    public function key(): int
    {
        return $this->stdIn->key();
    }

    public function valid(): bool
    {
        return $this->stdIn->valid();
    }

    public function rewind(): void
    {
        $this->stdIn = new SplFileObject('php://stdin');
        $this->stdIn->setFlags(SplFileObject::DROP_NEW_LINE);
    }

    public function extract(EtlState $state): iterable
    {
        yield from $this;
    }
}
