<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use EmptyIterator;
use SplFileObject;

use function is_iterable;
use function is_string;
use function substr;

final readonly class JSONExtractor implements IterableExtractorInterface
{
    public function __construct(
        public mixed $source = null,
    ) {
    }

    public function extract(EtlState $state): iterable
    {
        $content = $source = $state->source ?? $this->source;

        $source = $this->resolveFile($source);
        if ($source instanceof SplFileObject) {
            $content = $source->fread($source->getSize());
        }

        if (is_string($content)) {
            $content = json_decode($content, true);
        }

        if (null === $content) {
            return new EmptyIterator();
        }

        if (!is_iterable($content)) {
            throw new ExtractException('Provided JSON is not iterable.');
        }

        yield from $content;
    }

    private function resolveFile(mixed $source): ?SplFileObject
    {
        return match (true) {
            $source instanceof SplFileObject => $source,
            is_string($source) && str_starts_with($source, 'file://') => new SplFileObject(substr($source, 7)),
            default => null,
        };
    }
}
