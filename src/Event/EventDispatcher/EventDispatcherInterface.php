<?php

namespace BenTools\ETL\Event\EventDispatcher;

interface EventDispatcherInterface {

    public function trigger(EventInterface $event);

}