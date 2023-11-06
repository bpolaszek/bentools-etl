<?php

declare(strict_types=1);

namespace Bentools\ETL;

use function array_fill_keys;
use function array_intersect_key;
use function array_replace;

/**
 * @param list<string>         $keys
 * @param array<string, mixed> $values
 * @param array<string, mixed> ...$extraValues
 *
 * @return array<string, mixed>
 */
function array_fill_from(array $keys, array $values, array ...$extraValues): array
{
    $defaults = array_fill_keys($keys, null);
    $values = array_replace($values, ...$extraValues);

    return array_intersect_key($values, $defaults);
}
