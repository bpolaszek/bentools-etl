<?php

namespace BenTools\ETL\Tests;

use BenTools\ETL\Etl;

/**
 * @param array $data
 * @return \Generator
 */
function create_generator(array $data): \Generator
{
    return (function ($data) {
        yield from $data;
    })($data);
}

/**
 * @return Etl
 * @throws \InvalidArgumentException
 * @throws \RuntimeException
 */
function dummy_etl(): Etl
{
    return new Etl();
}
