<?php

declare(strict_types=1);

namespace Bentools\ETL\Normalizer;

use function is_numeric;
use function settype;

final readonly class NumericStringToNumberNormalizer implements ValueNormalizerInterface
{
    public function normalize(mixed $value): mixed
    {
        if (is_numeric($value)) {
            settype($value, str_contains($value, '.') ? 'float' : 'int');
        }

        return $value;
    }
}
