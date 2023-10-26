<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Recipe;

use Bentools\ETL\EtlExecutor;

use function expect;

it('uses a recipe', function () {
    // Given
    $toggle = 'off';
    $executor = (new EtlExecutor())->withRecipe(
        function (EtlExecutor $executor) use (&$toggle) {
            return $executor->onInit(function () use (&$toggle) {
                $toggle = 'on';
            });
        },
    );

    // When
    $executor->process([]);

    // Then
    expect($toggle)->toBe('on');
});
