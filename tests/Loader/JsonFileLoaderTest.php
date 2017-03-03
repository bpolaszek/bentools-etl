<?php

namespace BenTools\ETL\Tests\Loader;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\ETLEvents;
use BenTools\ETL\Event\EventDispatcher\Bridge\Symfony\SymfonyEvent;
use BenTools\ETL\Event\EventDispatcher\Bridge\Symfony\SymfonyEventDispatcherBridge as SymfonyBridge;
use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Iterator\CsvFileIterator;
use BenTools\ETL\Loader\JsonFileLoader;
use BenTools\ETL\Runner\ETLRunner;
use PHPUnit\Framework\TestCase;
use SplFileObject;
use SplTempFileObject;

class JsonFileLoaderTest extends TestCase
{

    public function testLoader()
    {

        $keys            = [];
        $eventDispatcher = new SymfonyBridge();
        $eventDispatcher->getWrappedDispatcher()->addListener(ETLEvents::AFTER_EXTRACT, function (SymfonyEvent $event) use (&$keys) {
            if (empty($keys)) {
                $contextElement = $event->getWrappedEvent()->getElement();
                $keys           = array_values($contextElement->getData());
                $contextElement->skip();
            }
        });
        $items       = new CsvFileIterator(new SplFileObject(__DIR__ . '/../data/dictators.csv'));
        $extractor   = new KeyValueExtractor();
        $transformer = function (ContextElementInterface $element) use (&$keys) {
            $data = array_combine($keys, $element->getData());
            $element->setData($data);
            $element->setId(strtolower($data['country']));
        };
        $output      = new SplTempFileObject();
        $loader      = new JsonFileLoader($output, JSON_PRETTY_PRINT);
        $run         = new ETLRunner(null, $eventDispatcher);
        $run($items, $extractor, $transformer, $loader);

        $compared = file_get_contents(__DIR__ . '/../data/dictators.json');

        $output->rewind();
        $generated = implode(null, iterator_to_array($output));
        $this->assertSame($compared, $generated);
    }

}
