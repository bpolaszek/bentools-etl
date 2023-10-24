<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Iterator;

use Bentools\ETL\Iterator\StrTokIterator;

use function expect;

it('yields lines of a text, but removes empty lines', function () {
    // Given
    $text = <<<EOF
foo


bar
EOF;

    $items = [...new StrTokIterator($text)];

    expect($items)->toBe([
        'foo',
        'bar',
    ]);
});
