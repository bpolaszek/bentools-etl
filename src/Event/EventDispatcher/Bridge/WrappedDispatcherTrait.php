<?php

namespace BenTools\ETL\Event\EventDispatcher\Bridge;

use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

trait WrappedDispatcherTrait
{
    /**
     * @var SymfonyEventDispatcherInterface
     */
    protected $wrappedDispatcher;

    /**
     * @inheritDoc
     */
    public function __call($name, $arguments)
    {
        $wrappedEvent = $this->wrappedDispatcher;
        return $wrappedEvent->$name(...$arguments);
    }

    /**
     * @return SymfonyEventDispatcherInterface
     */
    public function getWrappedDispatcher()
    {
        return $this->wrappedDispatcher;
    }
}
