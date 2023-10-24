<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior\Events;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EtlState;
use Bentools\ETL\EventDispatcher\Event\FlushExceptionEvent;
use Bentools\ETL\Loader\LoaderInterface;
use RuntimeException;

use function expect;
use function it;

it('can resume processing by unsetting the flush exception', function () {
    $items = ['banana', 'apple', 'strawberry', 'raspberry', 'peach'];
    $executor = (new EtlExecutor(options: new EtlConfiguration(flushEvery: 2)))
        ->loadInto(new FlushFailsLoader())
        ->onFlushException(function (FlushExceptionEvent $event) {
            $event->removeException();
        })
    ;
    $report = $executor->process($items);
    expect($report->output)->toBe([
        ['strawberry', 'raspberry'],
        ['peach'],
    ]);
});

class FlushFailsLoader implements LoaderInterface
{
    public function load(mixed $item, EtlState $state): void
    {
        $state->context['pending'][] = $item;
    }

    /**
     * @return list<list<string>>
     */
    public function flush(bool $isPartial, EtlState $state): array
    {
        $state->context['batchNumber'] ??= 0;
        $state->context['hasFailed'] ??= false;

        // Trigger failure on 1st flush
        if (!$state->context['hasFailed']) {
            $state->context['hasFailed'] = true;
            $state->context['pending'] = [];
            throw new RuntimeException('Flush failed.');
        }
        foreach ($state->context['pending'] as $key => $value) {
            $state->context['batches'][$state->context['batchNumber']][] = $value;
        }
        $state->context['pending'] = [];
        ++$state->context['batchNumber'];

        return $state->context['batches'];
    }
}
