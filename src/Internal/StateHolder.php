<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlState;

/**
 * @internal
 */
final class StateHolder
{
    public function __construct(
        public ?EtlState $state = null,
    ) {
    }
}
