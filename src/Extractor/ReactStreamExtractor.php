<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;
use React\Stream\ReadableStreamInterface;

final readonly class ReactStreamExtractor implements ExtractorInterface
{
    public function __construct(
        public ?ReadableStreamInterface $stream = null,
    ) {
    }

    public function extract(EtlState $state): ReadableStreamInterface
    {
        return $state->source ?? $this->stream;
    }
}
