<?php

namespace BenTools\ETL\Context;

class CallbackFactory extends KeyValueFactory implements ContextElementFactoryInterface {

    /**
     * @var callable
     */
    private $callback;

    /**
     * CallbackFactory constructor.
     * @param callable $callback
     * @param string $class
     */
    public function __construct(callable $callback, string $class = self::DEFAULT_CLASS) {
        parent::__construct($class);
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function createContext($identifier, $extractedData): ContextElementInterface {
        $element = parent::createContext($identifier, $extractedData);
        $callback = $this->callback;
        $result = $callback($element);
        if (null !== $result) {
            if (!$result instanceof ContextElementInterface) {
                throw new \RuntimeException(sprintf("The callback should either return nothing or a %s class.", ContextElementInterface::class));
            }
            else {
                $element = $result;
            }
        }
        return $element;
    }
}