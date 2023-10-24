<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EventDispatcher\Event\FlushEvent;

use function expect;

it('fires a flush event', function () {
    $flushEventsCounter = 0;

    // Given
    $executor = (new EtlExecutor())
        ->withOptions(new EtlConfiguration(flushEvery: 2))
        ->onFlush(function (FlushEvent $e) use (&$flushEventsCounter) {
            ++$flushEventsCounter;
        });

    // When
    $executor->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);

    // Then
    expect($flushEventsCounter)->toBe(3);
});
