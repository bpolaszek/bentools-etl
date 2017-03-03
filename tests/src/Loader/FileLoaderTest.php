<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Extractor\IncrementorExtractor;
use BenTools\ETL\Runner\ETLRunner;
use BenTools\ETL\Loader\FileLoader;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;

class FileLoaderTest extends TestCase
{
    public function testLoader()
    {
        $items     = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];
        $extractor = new IncrementorExtractor();
        $file      = new SplTempFileObject();
        $loader    = new FileLoader($file);
        $run       = new ETLRunner();
        $run($items, $extractor, null, $loader);

        $file->rewind();
        $this->assertEquals(implode('', [
            'bar',
            'baz'
        ]), implode('', iterator_to_array($file)));
    }
}
