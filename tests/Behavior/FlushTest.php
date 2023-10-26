<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Behavior;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\EtlExecutor;
use Bentools\ETL\EtlState;
use Bentools\ETL\EventDispatcher\Event\ExtractEvent;
use Bentools\ETL\Loader\LoaderInterface;

use function expect;

it('flushes items at the right time', function () {
    $loader = new BatchLoader();

    // Given
    $etl = new EtlExecutor(loader: $loader, options: new EtlConfiguration(flushEvery: 2));

    // When
    $report = $etl->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);

    // Then
    expect($report->output)->toBeArray()
        ->and($report->output)->toHaveCount(3)
        ->and($report->output[0])->toBe(['banana', 'apple'])
        ->and($report->output[1])->toBe(['strawberry', 'raspberry'])
        ->and($report->output[2])->toBe(['peach']);
});

it('forces flushes', function () {
    $loader = new BatchLoader();

    // Given
    $etl = (new EtlExecutor(loader: $loader, options: new EtlConfiguration(flushEvery: 2)))
        ->onExtract(function (ExtractEvent $event) {
            if (0 === $event->state->currentItemIndex) {
                $event->state->flush();
            }
        });

    // When
    $report = $etl->process(['banana', 'apple', 'strawberry', 'raspberry', 'peach']);

    // Then
    expect($report->output)->toBeArray()
        ->and($report->output)->toHaveCount(3)
        ->and($report->output[0])->toBe(['banana'])
        ->and($report->output[1])->toBe(['apple', 'strawberry'])
        ->and($report->output[2])->toBe(['raspberry', 'peach']);
});

class BatchLoader implements LoaderInterface
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
        foreach ($state->context['pending'] as $key => $value) {
            $state->context['batches'][$state->context['batchNumber']][] = $value;
        }
        $state->context['pending'] = [];
        ++$state->context['batchNumber'];

        return $state->context['batches'];
    }
}
