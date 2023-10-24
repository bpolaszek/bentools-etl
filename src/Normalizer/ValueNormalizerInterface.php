<?php

declare(strict_types=1);

namespace Bentools\ETL\Normalizer;

interface ValueNormalizerInterface
{
    public function normalize(mixed $value): mixed;
}
