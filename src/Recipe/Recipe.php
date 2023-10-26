<?php

declare(strict_types=1);

namespace Bentools\ETL\Recipe;

use Bentools\ETL\EtlExecutor;
use Closure;

abstract class Recipe
{
    abstract public function fork(EtlExecutor $executor): EtlExecutor;

    public static function fromCallable(callable $recipe): self
    {
        return new class(Closure::fromCallable($recipe)) extends Recipe {
            public function __construct(
                private Closure $recipe,
            ) {
            }

            public function fork(EtlExecutor $executor): EtlExecutor
            {
                return ($this->recipe)($executor);
            }
        };
    }
}
