<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader;

use BenTools\ETL\Exception\LoadException;
use BenTools\ETL\Loader\STDOUTLoader;
use BenTools\ETL\Tests\Stubs\STDOUTStub;

use function BenTools\ETL\loadInto;
use function BenTools\ETL\stdOut;
use function expect;

it('writes to STDOUT', function () {
    // Given
    $executor = loadInto(stdOut());
    $input = [
        'It',
        '',
        'Works!',
    ];

    $expected = <<<EOF
It

Works!

EOF;

    // When
    $output = STDOUTStub::emulate(fn ($resource) => $executor->process($input, context: [
        STDOUTLoader::class => [
            'resource' => $resource, // fake php://stdout
        ],
    ]));

    // Then
    expect($output)->toBe($expected);
});

it('cannot load something which is not a string', fn () => loadInto(stdOut())->process([[]]))
->throws(LoadException::class, 'Expected string, got array.');
