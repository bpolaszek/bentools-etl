<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Context\ContextElementInterface;

/**
 * Class ObjectPropertyExtractor
 * Sets the identifier based on a property of the item (it can be an array or an object).
 */
class ObjectPropertyExtractor extends KeyValueExtractor implements ExtractorInterface
{

    /**
     * @var string
     */
    protected $property;

    /**
     * ObjectPropertyExtractor constructor.
     *
     * @param string $property
     * @param string $class
     */
    public function __construct(string $property, string $class = self::DEFAULT_CLASS)
    {
        parent::__construct($class);
        $this->property = $property;
    }

    /**
     * @inheritdoc
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

        if (is_object($value)) {
            if (!property_exists($value, $this->property)) {
                throw new \RuntimeException(sprintf('This object does not contain a \'%s\' property', $this->property));
            }
            $element->setId($value->{$this->property});
        }

        $element->setData($value);
        return $element;
    }
}
