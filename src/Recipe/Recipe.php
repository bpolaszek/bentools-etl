<?php

declare(strict_types=1);

namespace BenTools\ETL\Recipe;

use BenTools\ETL\EtlExecutor;
use Closure;

abstract class Recipe
{
    abstract public function decorate(EtlExecutor $executor): EtlExecutor;

    final public static function fromCallable(callable $recipe): self
    {
        return new class($recipe(...)) extends Recipe {
            public function __construct(
                private readonly Closure $recipe,
            ) {
            }

            public function decorate(EtlExecutor $executor): EtlExecutor
            {
                return ($this->recipe)($executor);
            }
        };
    }
}
