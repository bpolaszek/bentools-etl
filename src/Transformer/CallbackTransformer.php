<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Context\ContextElementInterface;

class CallbackTransformer implements TransformerInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * CallbackTransformer constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContextElementInterface $element): void
    {
        $callback = $this->callback;
        $element->setData($callback($element->getData()));
    }
}
