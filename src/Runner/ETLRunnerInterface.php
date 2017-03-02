<?php

namespace BenTools\ETL\Runner;

use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Transformer\TransformerInterface;

interface ETLRunnerInterface
{

    /**
     * @param iterable|\Generator|\Traversable|array $items
     * @param callable|ExtractorInterface            $extractor
     * @param callable|TransformerInterface          $transformer
     * @param callable                               $loader
     */
    public function __invoke(iterable $items, callable $extractor, callable $transformer = null, callable $loader);
}
