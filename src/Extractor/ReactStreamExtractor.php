<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Iterator\IteratorStream;
use React\Stream\ReadableStreamInterface;

final readonly class ReactStreamExtractor implements ExtractorInterface
{
    /**
     * @param iterable<mixed>|ReadableStreamInterface|null $stream
     */
    public function __construct(
        public ReadableStreamInterface|iterable|null $stream = null,
    ) {
    }

    public function extract(EtlState $state): ReadableStreamInterface
    {
        return $this->ensureStream($state->source ?? $this->stream);
    }

    /**
     * @param iterable<mixed>|ReadableStreamInterface $items
     */
    private function ensureStream(iterable|ReadableStreamInterface $items): ReadableStreamInterface
    {
        return $items instanceof ReadableStreamInterface ? $items : new IteratorStream($items);
    }
}
