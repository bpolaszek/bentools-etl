<?php

declare(strict_types=1);

namespace Bentools\ETL\Normalizer;

final readonly class EmptyStringToNullNormalizer implements ValueNormalizerInterface
{
    public function normalize(mixed $value): mixed
    {
        if ('' === $value) {
            return null;
        }

        return $value;
    }
}
