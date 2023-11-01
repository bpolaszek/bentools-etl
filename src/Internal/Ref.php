<?php

declare(strict_types=1);

namespace Bentools\ETL\Internal;

/**
 * @template T
 *
 * @internal
 */
final class Ref
{
    /**
     * @param T $value
     */
    private function __construct(
        public mixed $value,
    ) {
    }

    /**
     * @param T $value
     *
     * @return self<T>
     */
    public function replaceWith(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param T $value
     *
     * @return self<T>
     */
    public static function create(mixed $value): self
    {
        static $prototype;
        $prototype ??= new self(null);

        $ref = clone $prototype;
        $ref->value = $value;

        return $ref;
    }
}
