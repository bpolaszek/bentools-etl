<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\ContextElementEvent;
use BenTools\ETL\Event\ETLEvents;
use BenTools\ETL\Event\EventDispatcher\ETLEventDispatcher;
use BenTools\ETL\Extractor\IncrementorExtractor;
use BenTools\ETL\Iterator\CsvFileIterator;
use BenTools\ETL\Loader\JsonFileLoader;
use BenTools\ETL\Runner\ETLRunner;
use function BenTools\ETL\Tests\create_generator;
use function BenTools\ETL\Tests\dummy_etl;
use BenTools\ETL\Tests\TestSuite;
use PHPUnit\Framework\TestCase;
use SplFileObject;
use SplTempFileObject;

class JsonFileLoaderTest extends TestCase
{

    public function testLoader()
    {
        $file = new SplTempFileObject();
        $loader = JsonFileLoader::toFile($file);
        $data = ['foo', 'bar'];
        foreach ($data as $key => $value) {
            $loader->load(create_generator([$key => $value]), $key, dummy_etl());
        }
        $loader->commit(false);
        $file->rewind();
        $content = '';
        while (!$file->eof()) {
            $content .= $file->fgets();
        }
        $this->assertEquals(json_encode($data), trim($content));
    }

}
