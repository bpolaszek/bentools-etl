<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use BenTools\ETL\EtlExecutor;
use BenTools\ETL\EventDispatcher\Event\ExtractEvent;

use function expect;

it('fires an extract event', function () {
    $items = [2 => 'foo', 3 => 'bar'];
    $extractedItems = [];

    // Given
    $executor = (new EtlExecutor())
        ->onExtract(function (ExtractEvent $event) use (&$extractedItems) {
            $extractedItems[$event->state->currentItemKey] = $event->item;
        });

    // When
    $executor->process($items);

    // Then
    expect($extractedItems)->toBe($items);
});
