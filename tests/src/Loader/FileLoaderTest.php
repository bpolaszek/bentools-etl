<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Extractor\IncrementorExtractor;
use BenTools\ETL\Runner\ETLRunner;
use BenTools\ETL\Loader\FileLoader;
use function BenTools\ETL\Tests\create_generator;
use function BenTools\ETL\Tests\dummy_etl;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;

class FileLoaderTest extends TestCase
{
    /**
     * @test
     */
    public function it_writes_new_lines_by_default()
    {
        $items     = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];
        $file      = new SplTempFileObject();
        $loader    = new FileLoader($file);

        foreach ($items as $key => $value) {
            $loader->load(create_generator([$key => $value]), $key, dummy_etl());
        }

        $file->rewind();
        $this->assertEquals(implode(\PHP_EOL, [
            'bar',
            'baz',
            ''
        ]), implode('', iterator_to_array($file)));
    }

    /**
     * @test
     */
    public function it_concats_all_strings()
    {
        $items     = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];
        $file      = new SplTempFileObject();
        $loader    = new FileLoader($file, ['eol' => FileLoader::NO_EOL]);

        foreach ($items as $key => $value) {
            $loader->load(create_generator([$key => $value]), $key, dummy_etl());
        }

        $file->rewind();
        $this->assertEquals(implode('', [
            'bar',
            'baz'
        ]), implode('', iterator_to_array($file)));
    }
}
