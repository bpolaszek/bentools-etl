<?php

namespace BenTools\ETL\Event\EventDispatcher\Bridge;


trait WrappedDispatcherTrait
{
    /**
     * @var mixed
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
     * @return mixed
     */
    public function getWrappedDispatcher()
    {
        return $this->wrappedDispatcher;
    }
}
