<?php

declare(strict_types=1);

namespace Bentools\ETL\Internal;

use ReflectionClass;

use function array_column;
use function array_combine;
use function array_fill;
use function array_intersect_key;
use function count;
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
     * @param array<string, mixed> $overridenProps
     */
    private function clone(array $overridenProps = []): self
    {
        static $refl, $constructorParams, $emptyProps;
        $refl ??= new ReflectionClass($this);
        $constructorParams ??= array_column($refl->getConstructor()->getParameters(), 'name');
        $emptyProps ??= array_combine($constructorParams, array_fill(0, count($constructorParams), null));

        return new self(...($overridenProps + array_intersect_key(get_object_vars($this), $emptyProps)));
    }
}
