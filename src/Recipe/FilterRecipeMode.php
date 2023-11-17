<?php

declare(strict_types=1);

namespace BenTools\ETL\Recipe;

/**
 * @internal
 */
enum FilterRecipeMode
{
    case INCLUDE;
    case EXCLUDE;
}
