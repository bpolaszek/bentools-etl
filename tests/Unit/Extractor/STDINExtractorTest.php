<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Extractor;

use BenTools\ETL\Tests\Stubs\STDINStub;

use function BenTools\ETL\extractFrom;
use function BenTools\ETL\stdIn;
use function expect;

it('iterates over STDIN', function () {
    $content = <<<EOF
Hello

Everybody!
EOF;

    $executor = extractFrom(stdIn());
    $report = STDINStub::emulate($content, $executor->process(...));

    expect($report->output)->toBe([
        'Hello',
        '',
        'Everybody!',
    ]);
});
