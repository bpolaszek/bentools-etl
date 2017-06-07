<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Context\ContextElementInterface;
use IteratorAggregate;
use Traversable;

class TransformerStack implements TransformerInterface, IteratorAggregate
{
    private $transformers = [];

    /**
     * TransformerStack constructor.
     * @param iterable $transformers
     */
    public function __construct(iterable $transformers = [])
    {
        foreach ($transformers as $transformer) {
            $this->registerTransformer($transformer);
        }
    }

    /**
     * @param callable $transformer
     * @param int      $priority
     */
    public function registerTransformer(callable $transformer, int $priority = 0): void
    {
        $this->transformers[] = [
            'p' => $priority,
            'c' => $transformer,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        usort($this->transformers, function ($a, $b) {
            return $b['p'] <=> $a['p'];
        });

        foreach ($this->transformers as $transformer) {
            yield $transformer['c'];
        }
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContextElementInterface $element): void
    {
        foreach ($this as $transform) {
            if (!$element->shouldSkip() && !$element->shouldFlush()) {
                $transform($element);
            }
        }
    }
}
