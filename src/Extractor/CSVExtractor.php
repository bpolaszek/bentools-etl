<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Iterator\CSVIterator;

use function is_string;
use function str_starts_with;
use function substr;

final readonly class CSVExtractor implements ExtractorInterface
{
    /**
     * @param array{delimiter?: string, enclosure?: string, escapeString?: string, skipEmptyLines?: bool, columns?: 'auto'|string[]|null} $options
     */
    public function __construct(
        private ?string $content = null,
        private array $options = [],
    ) {
    }

    public function extract(EtlState $state): iterable
    {
        $content = $state->source ?? $this->content;

        if (!is_string($content)) {
            throw new ExtractException('Invalid source.');
        }

        if (str_starts_with($content, 'file://')) {
            $iterator = (new FileExtractor(substr($content, 7), $this->options))->extract($state);
        } else {
            $iterator = (new TextLinesExtractor($content, $this->options))->extract($state);
        }

        return new CSVIterator($iterator, $this->options);
    }
}
