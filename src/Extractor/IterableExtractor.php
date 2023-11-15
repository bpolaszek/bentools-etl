<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use EmptyIterator;

use function is_iterable;

final readonly class IterableExtractor implements IterableExtractorInterface
{
    /**
     * @param iterable<mixed> $source
     */
    public function __construct(
        public iterable $source = new EmptyIterator(),
    ) {
    }

    public function extract(EtlState $state): iterable
    {
        $source = $state->source ?? $this->source;

        if (!is_iterable($source)) {
            throw new ExtractException('Provided source is not iterable.');
        }

        return $source;
    }
}
