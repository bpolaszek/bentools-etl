<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Recipe;

use BenTools\ETL\EtlExecutor;

use function expect;

it('uses a recipe', function () {
    // Given
    $hasReceivedInitEvent = false;
    $hasReceivedEndEvent = false;
    $executor = (new EtlExecutor())->withRecipe(
        function (EtlExecutor $executor) use (&$hasReceivedInitEvent, &$hasReceivedEndEvent) {
            return $executor
                ->onInit(function () use (&$hasReceivedInitEvent) {
                    $hasReceivedInitEvent = true;
                })
                ->onEnd(function () use (&$hasReceivedEndEvent) {
                    $hasReceivedEndEvent = true;
                });
        },
    );

    // When
    $executor->process([]);

    // Then
    expect($hasReceivedInitEvent)->toBeTrue()
        ->and($hasReceivedEndEvent)->toBeTrue();
});
