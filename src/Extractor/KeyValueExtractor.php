<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Context\ContextElementInterface;

/**
 * Class KeyValueExtractor
 * Default extractor: sets the identifier from the key => value iterator (=> the key)
 */
class KeyValueExtractor implements ExtractorInterface
{

    const DEFAULT_CLASS = ContextElement::class;

    protected $class = self::DEFAULT_CLASS;

    /**
     * KeyValueExtractor constructor.
     *
     * @param string $class
     */
    public function __construct(string $class = self::DEFAULT_CLASS)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this - Provides Fluent Interface
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __invoke($key, $value): ContextElementInterface
    {
        $class = $this->getClass();
        if (!is_a($class, ContextElementInterface::class, true)) {
            throw new \RuntimeException(sprintf('%s should implement %s.', $class, ContextElementInterface::class));
        }
        /**
         * @var ContextElementInterface $element
         */
        $element = new $class;
        $element->setId($key);
        $element->setExtractedData($value);
        return $element;
    }
}
