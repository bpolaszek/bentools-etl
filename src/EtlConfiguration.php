<?php

declare(strict_types=1);

namespace Bentools\ETL;

use InvalidArgumentException;

use function is_float;
use function sprintf;

use const INF;

final readonly class EtlConfiguration
{
    public float|int $flushFrequency;

    public function __construct(
        float|int $flushEvery = INF,
    ) {
        if (INF !== $flushEvery && is_float($flushEvery)) {
            throw new InvalidArgumentException('Expected \\INF or int, float given.');
        }
        if ($flushEvery < 1) {
            throw new InvalidArgumentException(sprintf('Expected positive integer > 0, got %d', $flushEvery));
        }
        $this->flushFrequency = $flushEvery;
    }
}
