<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit;

use Bentools\ETL\EtlConfiguration;
use InvalidArgumentException;

it('denies float values', function () {
    new EtlConfiguration(flushEvery: 2.1);
})->throws(InvalidArgumentException::class);

it('denies negative values', function () {
    new EtlConfiguration(flushEvery: -10);
})->throws(InvalidArgumentException::class);
