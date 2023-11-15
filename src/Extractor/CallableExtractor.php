<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlState;
use Closure;
use EmptyIterator;

use function is_iterable;

final readonly class CallableExtractor implements IterableExtractorInterface
{
    public function __construct(
        public Closure $closure,
    ) {
    }

    public function extract(EtlState $state): iterable
    {
        $extracted = ($this->closure)($state);

        if (null === $extracted) {
            return new EmptyIterator();
        }

        if (!is_iterable($extracted)) {
            return [$extracted];
        }

        return $extracted;
    }
}
