<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use ReflectionClass;
use ReflectionProperty;

use function array_column;
use function array_diff;
use function array_filter;
use function BenTools\ETL\array_fill_from;
use function get_object_vars;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
trait ClonableTrait
{
    /**
     * Create a clone.
     *
     * @param array<string, mixed> $cloneArgs
     */
    public function cloneWith(array $cloneArgs = []): static
    {
        static $refl, $writableProps, $writablePropNames, $constructorParamNames;
        $refl ??= new ReflectionClass($this);
        $constructorParamNames ??= array_column($refl->getConstructor()->getParameters(), 'name');
        $writableProps ??= array_filter(
            $refl->getProperties(),
            fn (ReflectionProperty $property) => !$property->isReadOnly(),
        );
        $writablePropNames ??= array_diff(array_column($writableProps, 'name'), $constructorParamNames);

        $clone = new static(...array_fill_from($constructorParamNames, get_object_vars($this), $cloneArgs));
        $notPromotedProps = array_fill_from($writablePropNames, get_object_vars($this), $cloneArgs);
        foreach ($notPromotedProps as $prop => $value) {
            $clone->{$prop} = $value;
        }

        return $clone;
    }
}
