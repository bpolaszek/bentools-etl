<?php

declare(strict_types=1);

namespace BenTools\ETL;

use BenTools\ETL\Internal\Ref;

use function array_fill_keys;
use function array_intersect_key;
use function array_replace;

/**
 * @internal
 *
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

/**
 * @internal
 *
 * @template T
 *
 * @param T $value
 *
 * @return Ref<T>
 */
function ref(mixed $value): Ref
{
    return Ref::create($value);
}

/**
 * @internal
 *
 * @template T
 *
 * @param Ref<T> $ref
 *
 * @return T
 */
function unref(Ref $ref): mixed
{
    return $ref->value;
}
