<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Context\ContextElementInterface;

/**
 * Class CallbackExtractor
 * Sets the identifier via a callback
 */
class CallbackExtractor extends KeyValueExtractor implements ExtractorInterface
{

    /**
     * @var callable
     */
    private $callback;

    /**
     * CallbackExtractor constructor.
     *
     * @param callable $callback
     * @param string   $class
     */
    public function __construct(callable $callback, string $class = self::DEFAULT_CLASS)
    {
        parent::__construct($class);
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function __invoke($key, $value): ContextElementInterface
    {
        $element = parent::__invoke($key, $value);
        $callback = $this->callback;
        $result = $callback($element);
        if (null !== $result) {
            if (!$result instanceof ContextElementInterface) {
                throw new \RuntimeException(sprintf("The callback should either return nothing or a %s class.", ContextElementInterface::class));
            } else {
                $element = $result;
            }
        }
        return $element;
    }
}
