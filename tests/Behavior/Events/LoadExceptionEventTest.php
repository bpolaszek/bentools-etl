<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\LoadExceptionEvent;
use BenTools\ETL\Exception\LoadException;

use function expect;
use function it;

it('can resume processing by unsetting the load exception', function () {
    $items = ['foo', 'bar', 'baz'];
    $loadedItems = [];
    $executor = (new EtlExecutor())
        ->loadInto(function (mixed $value) use (&$loadedItems) {
            if ('bar' === $value) {
                throw new LoadException('Cannot load `bar`.');
            }
            $loadedItems[] = $value;
        })
        ->onLoadException(function (LoadExceptionEvent $event) {
            $event->removeException();
        })
    ;
    $executor->process($items);

    expect($loadedItems)->toBe(['foo', 'baz']);
});
