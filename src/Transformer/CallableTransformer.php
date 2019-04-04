<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Etl;

final class CallableTransformer implements TransformerInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * CallableTransformer constructor.
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @inheritDoc
     */
    public function transform($value, $key, Etl $etl): \Generator
    {
        yield $key => ($this->callable)($value);
    }
}
