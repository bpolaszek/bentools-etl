<?php

declare(strict_types=1);

namespace BenTools\ETL\Processor;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EtlState;
use BenTools\ETL\Exception\ExtractException;
use BenTools\ETL\Exception\SkipRequest;
use BenTools\ETL\Transformer\BatchTransformerInterface;
use Generator;
use Throwable;

use function BenTools\IterableFunctions\iterable_chunk;
use function is_iterable;

/**
 * @internal
 */
final readonly class IterableProcessor implements ProcessorInterface
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
        if ($executor->transformer instanceof BatchTransformerInterface) {
            $batchSize = $executor->options->batchSize;
            foreach (iterable_chunk($this->extract($executor, $state, $items), $batchSize, true) as $chunk) {
                try {
                    $executor->processItemBatch($chunk, $state);
                } catch (SkipRequest) {
                }
            }
        } else {
            foreach ($this->extract($executor, $state, $items) as $key => $item) {
                try {
                    $executor->processItem($item, $key, $state);
                } catch (SkipRequest) {
                }
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
