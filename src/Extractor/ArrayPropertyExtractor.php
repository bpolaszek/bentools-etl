<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Context\ContextElementInterface;

class ArrayPropertyExtractor extends KeyValueExtractor implements ExtractorInterface
{

    /**
     * @var string
     */
    protected $property;

    /**
     * @var bool
     */
    private $shift = true;

    /**
     * ObjectPropertyExtractor constructor.
     *
     * @param string $property
     * @param bool   $shift
     * @param string $class
     */
    public function __construct(string $property, bool $shift = true, string $class = self::DEFAULT_CLASS)
    {
        parent::__construct($class);
        $this->property = $property;
        $this->shift = $shift;
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

        if (is_array($value)) {
            if (!array_key_exists($this->property, $value)) {
                throw new \RuntimeException(sprintf('This array does not contain a \'%s\' property', $this->property));
            }
            $element->setId($value[$this->property]);
            if (true === $this->shift) {
                unset($value[$this->property]);
            }
        }

        $element->setData($value);
        return $element;
    }
}
