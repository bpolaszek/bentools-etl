<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Loader\CsvFileLoader;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;
use function BenTools\ETL\Tests\create_generator;
use function BenTools\ETL\Tests\dummy_etl;

class CsvFileLoaderTest extends TestCase
{

    public function testLoaderWithoutKeys()
    {
        $file = new SplTempFileObject();
        $loader = CsvFileLoader::toFile($file, ['delimiter' => '|']);
        $data = [
            ['Bill', 'Clinton'],
            ['Richard', 'Nixon'],
        ];

        $loader->load(create_generator($data), null, dummy_etl());

        $file->rewind();

        $expected = [
            'Bill|Clinton' . PHP_EOL,
            'Richard|Nixon' . PHP_EOL,
        ];
        $this->assertEquals($expected, iterator_to_array($file));

    }
}
