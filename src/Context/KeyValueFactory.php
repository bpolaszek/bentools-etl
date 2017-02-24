<?php

namespace BenTools\ETL\Context;

class KeyValueFactory implements ContextElementFactoryInterface {

    const DEFAULT_CLASS = ContextElement::class;

    protected $class = self::DEFAULT_CLASS;

    /**
     * KeyValueFactory constructor.
     * @param string $class
     */
    public function __construct(string $class = self::DEFAULT_CLASS) {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this - Provides Fluent Interface
     */
    public function setClass($class) {
        $this->class = $class;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createContext($identifier, $extractedData): ContextElementInterface {
        $class = $this->getClass();
        if (!is_a($class, ContextElementInterface::class, true)) {
            throw new \RuntimeException(sprintf('%s should implement %s.', $class, ContextElementInterface::class));
        }
        $element = new ContextElement();
        $element->setIdentifier($identifier);
        $element->setExtractedData($extractedData);
        return $element;
    }
}