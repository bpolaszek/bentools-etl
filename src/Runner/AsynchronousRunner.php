<?php

namespace BenTools\ETL\Runner;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Asynchronous runner
 * Requires guzzlehttp/promises to work. (cf composer.json)
 */
class AsynchronousRunner extends ETLRunner
{

    /**
     * @inheritDoc
     */
    public function __invoke(
        iterable $items,
        callable $extractor,
        callable $transformer = null,
        callable $loader
    ): PromiseInterface {
    
        $promise = new Promise(
            function () use (&$promise, &$items, $extractor, $transformer, $loader) {
                $promise->resolve(parent::__invoke($items, $extractor, $transformer, $loader));
            }
        );
        return $promise;
    }
}
