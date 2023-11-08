<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Iterator;

use BenTools\ETL\Iterator\PregSplitIterator;

use function expect;

it('yields lines of a text', function () {
    // Given
    $text = <<<EOF
foo


bar
EOF;

    $items = [...new PregSplitIterator($text)];

    expect($items)->toBe([
        'foo',
        '',
        '',
        'bar',
    ]);
});
