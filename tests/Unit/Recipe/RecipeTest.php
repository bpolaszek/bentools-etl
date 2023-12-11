<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Recipe;

use BenTools\ETL\EtlExecutor;

use function BenTools\ETL\withRecipe;
use function expect;

it('uses a recipe', function () {
    // Given
    $hasReceivedInitEvent = false;
    $hasReceivedEndEvent = false;
    $executor = withRecipe(
        function (EtlExecutor $executor) use (&$hasReceivedInitEvent, &$hasReceivedEndEvent) {
            return $executor
                ->onInit(function () use (&$hasReceivedInitEvent) {
                    $hasReceivedInitEvent = true;
                })
                ->onEnd(function () use (&$hasReceivedEndEvent) {
                    $hasReceivedEndEvent = true;
                });
        },
        fn (EtlExecutor $executor) => $executor->withContext(['foo' => 'bar'])
    );

    // When
    $report = $executor->process([]);

    // Then
    expect($hasReceivedInitEvent)->toBeTrue()
        ->and($hasReceivedEndEvent)->toBeTrue()
        ->and($report->context)->toBe(['foo' => 'bar'])
    ;
});
