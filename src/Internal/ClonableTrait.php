<?php

declare(strict_types=1);

namespace Bentools\ETL\Internal;

use ReflectionClass;

use function array_column;
use function array_combine;

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
        static $refl, $properties, $constructorParams;
        $refl ??= new ReflectionClass($this);
        $properties ??= array_combine(array_column($refl->getProperties(), 'name'), $refl->getProperties());
        $constructorParams ??= $refl->getConstructor()->getParameters();

        $args = (function () use ($properties, $constructorParams) {
            foreach ($constructorParams as $param) {
                $key = $param->getName();
                yield $key => $properties[$key]->getValue($this);
            }
        })();

        return new self(...[
            ...$args,
            ...$overridenProps,
        ]);
    }
}
