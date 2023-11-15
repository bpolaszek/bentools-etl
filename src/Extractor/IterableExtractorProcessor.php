<?php

declare(strict_types=1);

namespace BenTools\ETL\Extractor;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Exception\SkipRequest;
use Generator;
use Throwable;

use function is_iterable;

final readonly class IterableExtractorProcessor implements ExtractorProcessorInterface
{
    public function supports(mixed $extracted): bool
    {
        return is_iterable($extracted);
    }

    /**
     * @param iterable<mixed> $items
     */
    public function process(EtlExecutor $executor, EtlState $state, mixed $items): EtlState
    {
        foreach ($this->extract($executor, $state, $items) as $key => $item) {
            try {
                $executor->processItem($item, $key, $state);
            } catch (SkipRequest) {
            }
        }

        return $state;
    }

    /**
     * @param iterable<mixed> $items
     */
    public function extract(EtlExecutor $executor, EtlState $state, iterable $items): Generator
    {
        try {
            yield from $items;
        } catch (Throwable $exception) {
            ExtractException::emit($executor, $exception, $state);
        }
    }
}
