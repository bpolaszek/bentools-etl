<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EventDispatcher\Event\ExtractExceptionEvent;
use Bentools\ETL\Exception\ExtractException;
use RuntimeException;

it('catches an extract exception and return another', function () {
    $items = function () {
        yield 'foo';
        throw new RuntimeException('Something bad happened.');
    };

    $executor = (new EtlExecutor())->onExtractException(function (ExtractExceptionEvent $event) {
        $event->exception = new ExtractException('It miserably failed.');
    });
    $executor->process($items());
})->throws(ExtractException::class, 'It miserably failed.');
