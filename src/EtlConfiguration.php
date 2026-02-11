<?php

declare(strict_types=1);

namespace BenTools\ETL;

use InvalidArgumentException;

use function is_float;
use function sprintf;

use const INF;

class EtlConfiguration
{
    public readonly float|int $flushFrequency;
    public readonly int $batchSize;

    public function __construct(
        float|int $flushEvery = INF,
        int $batchSize = 1,
    ) {
        if (INF !== $flushEvery && is_float($flushEvery)) {
            throw new InvalidArgumentException('Expected \\INF or int, float given.');
        }
        if ($flushEvery < 1) {
            throw new InvalidArgumentException(sprintf('Expected positive integer > 0, got %d', $flushEvery));
        }
        if ($batchSize < 1) {
            throw new InvalidArgumentException(sprintf('Expected positive integer > 0 for batchSize, got %d', $batchSize));
        }
        $this->flushFrequency = $flushEvery;
        $this->batchSize = $batchSize;
    }
}
