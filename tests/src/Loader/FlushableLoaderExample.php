<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Loader\FlushableLoaderInterface;

class FlushableLoaderExample implements FlushableLoaderInterface
{
    private $flushEvery = 1;
    private $waitingElements = [];
    private $flushedElements = [];

    public function setFlushEvery(int $flushEvery)
    {
        $this->flushEvery = $flushEvery;
    }

    public function shouldFlushAfterLoad(): bool
    {
        return 0 !== $this->flushEvery // Otherwise we'll wait on an explicit flush() call
            && 0 === (count($this->waitingElements) % $this->flushEvery);
    }

    public function flush(): void
    {
        $this->flushedElements = array_merge($this->flushedElements, $this->waitingElements);
        $this->waitingElements = [];
    }

    public function __invoke(ContextElementInterface $element): void
    {
        $this->waitingElements[] = $element->getData();
    }

    public function getWaitingElements()
    {
        return $this->waitingElements;
    }

    public function getFlushedElements()
    {
        return $this->flushedElements;
    }

}