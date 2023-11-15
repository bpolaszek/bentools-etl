<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;

final readonly class ChainExtractor implements IterableExtractorInterface
{
    /**
     * @var ExtractorInterface[]
     */
    private array $extractors;

    public function __construct(
        ExtractorInterface|callable $extractor,
        ExtractorInterface|callable ...$extractors,
    ) {
        $extractors = [$extractor, ...$extractors];
        foreach ($extractors as $e => $_extractor) {
            if (!$_extractor instanceof ExtractorInterface) {
                $extractors[$e] = new CallableExtractor($_extractor(...));
            }
        }
        $this->extractors = $extractors;
    }

    public function with(
        ExtractorInterface|callable $extractor,
        ExtractorInterface|callable ...$extractors,
    ): self {
        return new self(...[...$this->extractors, $extractor, ...$extractors]);
    }

    public function extract(EtlState $state): iterable
    {
        foreach ($this->extractors as $extractor) {
            foreach ($extractor->extract($state) as $item) {
                yield $item;
            }
        }
    }

    public static function from(ExtractorInterface $extractor): self
    {
        return match ($extractor instanceof self) {
            true => $extractor,
            false => new self($extractor),
        };
    }
}
