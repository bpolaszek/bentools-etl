<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EventDispatcher\Event\TransformExceptionEvent;
use Bentools\ETL\Exception\TransformException;

use function expect;
use function it;

it('can resume processing by unsetting the transform exception', function () {
    $items = ['foo', 'bar', 'baz'];
    $loadedItems = [];
    $executor = (new EtlExecutor())
        ->transformWith(function (mixed $value) {
            if ('bar' === $value) {
                throw new TransformException('Cannot transform `bar`.');
            }
            yield $value;
        })
        ->loadInto(function (mixed $value) use (&$loadedItems) {
            $loadedItems[] = $value;
        })
        ->onTransformException(function (TransformExceptionEvent $event) {
            $event->removeException();
        })
    ;
    $executor->process($items);

    expect($loadedItems)->toBe(['foo', 'baz']);
});
