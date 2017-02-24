<?php

namespace BenTools\ETL\Context;

class PropertyFactory extends KeyValueFactory implements ContextElementFactoryInterface {

    /**
     * @var string
     */
    protected $property;

    /**
     * PropertyFactory constructor.
     * @param string $property
     * @param string $class
     */
    public function __construct(string $property, string $class = self::DEFAULT_CLASS) {
        parent::__construct($class);
        $this->property = $property;
    }

    /**
     * @inheritdoc
     */
    public function createContext($identifier, $extractedData): ContextElementInterface {
        $class = $this->getClass();
        if (!is_a($class, ContextElementInterface::class, true)) {
            throw new \RuntimeException(sprintf('%s should implement %s.', $class, ContextElementInterface::class));
        }

        $element = new ContextElement();

        if (is_object($extractedData)) {
            if (!property_exists($extractedData, $this->property)) {
                throw new \RuntimeException(sprintf('This object does not contain a \'%s\' property', $this->property));
            }
            $element->setIdentifier($extractedData->{$this->property});
        }
        elseif (is_array($extractedData)) {
            if (!array_key_exists($this->property, $extractedData)) {
                throw new \RuntimeException(sprintf('This array does not contain a \'%s\' property', $this->property));
            }
            $element->setIdentifier($extractedData[$this->property]);
        }

        $element->setExtractedData($extractedData);
        return $element;
    }

}