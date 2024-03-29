<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Iterator\FileIterator;
use SplFileObject;

use function is_string;

final readonly class FileExtractor implements IterableExtractorInterface
{
    /**
     * @param array{skipEmptyLines?: bool} $options
     */
    public function __construct(
        private string|SplFileObject|null $file,
        private array $options,
    ) {
    }

    public function extract(EtlState $state): iterable
    {
        $file = $state->source ?? $this->file;

        return new FileIterator($this->resolveFile($file), $this->options);
    }

    private function resolveFile(mixed $file): SplFileObject
    {
        return match (true) {
            $file instanceof SplFileObject => $file,
            is_string($file) => new SplFileObject($file),
            default => throw new ExtractException('Invalid file.'),
        };
    }
}
