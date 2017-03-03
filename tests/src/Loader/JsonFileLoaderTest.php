<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\ContextElementEvent;
use BenTools\ETL\Event\ETLEvents;
use BenTools\ETL\Event\EventDispatcher\ETLEventDispatcher;
use BenTools\ETL\Extractor\IncrementorExtractor;
use BenTools\ETL\Iterator\CsvFileIterator;
use BenTools\ETL\Loader\JsonFileLoader;
use BenTools\ETL\Runner\ETLRunner;
use BenTools\ETL\Tests\TestSuite;
use PHPUnit\Framework\TestCase;
use SplFileObject;
use SplTempFileObject;

class JsonFileLoaderTest extends TestCase
{

    public function testLoader()
    {

        $keys            = [];
        $eventDispatcher = new ETLEventDispatcher();
        $eventDispatcher->addListener(ETLEvents::AFTER_EXTRACT, function (ContextElementEvent $event) use (&$keys) {
            if (empty($keys)) {
                $contextElement = $event->getElement();
                $keys           = array_values($contextElement->getData());
                $contextElement->skip();
            }
        });
        $items       = new CsvFileIterator(new SplFileObject(TestSuite::getDataFile('dictators.csv')));
        $extractor   = new IncrementorExtractor();
        $transformer = function (ContextElementInterface $element) use (&$keys) {
            $data = array_combine($keys, $element->getData());
            $element->setData($data);
            $element->setId(strtolower($data['country']));
        };
        $output      = new SplTempFileObject();
        $loader      = new JsonFileLoader($output, JSON_PRETTY_PRINT);
        $run         = new ETLRunner(null, $eventDispatcher);
        $run($items, $extractor, $transformer, $loader);

        $compared = file_get_contents(TestSuite::getDataFile('dictators.json'));

        $output->rewind();
        $generated = implode(null, iterator_to_array($output));
        $this->assertSame($compared, $generated);
    }
}
