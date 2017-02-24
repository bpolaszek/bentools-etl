<?php

namespace BenTools\ETL\Runner;

use BenTools\ETL\Context\ContextElementFactoryInterface;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Transformer\TransformerInterface;

interface RunnerInterface {

    /**
     * Returns the factory used to create Context Element objects from extracted data.
     * @return ContextElementFactoryInterface
     */
    public function getFactory(): ContextElementFactoryInterface;

    /**
     * @param iterable|\Generator|\Traversable|array $extractor
     * @param callable|TransformerInterface $transformer
     * @param callable|LoaderInterface $loader
     * @return mixed
     */
    public function __invoke($extractor, $transformer, $loader);
}