<?php

declare(strict_types=1);

namespace Bentools\ETL\Loader;

use Bentools\ETL\EtlState;

interface LoaderInterface
{
    /**
     * Load a transformed item to its destination.
     */
    public function load(mixed $item, EtlState $state): void;

    /**
     * Flush pending items to destination.
     * The implementation MAY return a value when $isPartial is false (e.g. when the final flush() is called).
     * The returned value will be provided to the state at the end of the ETL execution.
     */
    public function flush(bool $isPartial, EtlState $state): mixed;
}
