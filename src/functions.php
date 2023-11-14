<?php

declare(strict_types=1);

namespace BenTools\ETL;

use BenTools\ETL\Extractor\ChainExtractor;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Extractor\STDINExtractor;
use BenTools\ETL\Internal\Ref;
use BenTools\ETL\Loader\ChainLoader;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Recipe\Recipe;
use BenTools\ETL\Transformer\ChainTransformer;
use BenTools\ETL\Transformer\TransformerInterface;

use function array_fill_keys;
use function array_intersect_key;
use function array_replace;
use function func_get_args;

/**
 * @param list<string>         $keys
 * @param array<string, mixed> $values
 * @param array<string, mixed> ...$extraValues
 *
 * @return array<string, mixed>
 *
 * @internal
 */
function array_fill_from(array $keys, array $values, array ...$extraValues): array
{
    $defaults = array_fill_keys($keys, null);
    $values = array_replace($values, ...$extraValues);

    return array_intersect_key($values, $defaults);
}

/**
 * @param T $value
 *
 * @return Ref<T>
 *
 * @internal
 *
 * @template T
 */
function ref(mixed $value): Ref
{
    return Ref::create($value);
}

/**
 * @param Ref<T> $ref
 *
 * @return T
 *
 * @internal
 *
 * @template T
 */
function unref(Ref $ref): mixed
{
    return $ref->value;
}

function extractFrom(ExtractorInterface|callable $extractor, ExtractorInterface|callable ...$extractors): EtlExecutor
{
    return (new EtlExecutor())->extractFrom(...func_get_args());
}

function transformWith(
    TransformerInterface|callable $transformer,
    TransformerInterface|callable ...$transformers
): EtlExecutor {
    return (new EtlExecutor())->transformWith(...func_get_args());
}

function loadInto(LoaderInterface|callable $loader, LoaderInterface|callable ...$loaders): EtlExecutor
{
    return (new EtlExecutor())->loadInto(...func_get_args());
}

function withRecipe(Recipe|callable $recipe): EtlExecutor
{
    return (new EtlExecutor())->withRecipe(...func_get_args());
}

function chain(ExtractorInterface|TransformerInterface|LoaderInterface $service,
): ChainExtractor|ChainTransformer|ChainLoader {
    return match (true) {
        $service instanceof ExtractorInterface => ChainExtractor::from($service),
        $service instanceof TransformerInterface => ChainTransformer::from($service),
        $service instanceof LoaderInterface => ChainLoader::from($service),
    };
}

function stdIn(): STDINExtractor
{
    return new STDINExtractor();
}
