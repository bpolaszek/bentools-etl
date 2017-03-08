<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Context\ContextElementInterface;

class IncrementorExtractor extends KeyValueExtractor implements ExtractorInterface
{
    /**
     * @var int
     */
    private $index = -1;

    /**
     * IncrementorExtractor constructor.
     */
    public function __construct(int $startAt = 0, string $class = self::DEFAULT_CLASS)
    {
        $this->index = $startAt - 1;
        parent::__construct($class);
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    public function __invoke($key, $value): ContextElementInterface
    {
        $element = parent::__invoke($key, $value);
        $element->setId(++$this->index);
        return $element;
    }
}
