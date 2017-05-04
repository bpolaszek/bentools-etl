<?php

namespace BenTools\ETL\Tests\Runner;

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Event\ContextElementEvent;
use BenTools\ETL\Event\ExtractExceptionEvent;
use BenTools\ETL\Extractor\IncrementorExtractor;
use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Loader\ArrayLoader;
use BenTools\ETL\Loader\FlushableLoaderInterface;
use BenTools\ETL\Runner\ETLRunner;
use BenTools\ETL\Tests\Loader\FlushableLoaderExample;
use PHPUnit\Framework\TestCase;

class ETLRunnerLoadExceptionsTest extends TestCase
{
    /**
     * @var ETLRunner
     */
    protected $runner;

    /**
     * @var callable
     */
    protected $scenario;

    /**
     * @var FlushableLoaderInterface
     */
    protected $loader;

    public function setUp()
    {
        $this->runner = new ETLRunner();
        $this->loader = new class extends FlushableLoaderExample
        {
            public function __invoke(ContextElementInterface $element): void
            {
                if ('bar' === $element->getData()) {
                    throw new \RuntimeException();
                }
                parent::__invoke($element);
            }

        };
        $this->scenario = function () {
            $data = [
                'foo',
                'bar',
                'baz',
            ];

            $extract = new IncrementorExtractor();
            $transform = function (ContextElementInterface $element) {
                return $element;
            };
            $load = $this->loader;
            $run = $this->runner;
            $run($data, $extract, $transform, $load);
        };
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadExceptionIsThrown()
    {
        $runScenario = $this->scenario;
        $runScenario();
    }

    public function testLoadExceptionIsCaughtAndSkipped()
    {
        $runScenario = $this->scenario;
        $runner = $this->runner;
        $runner->onLoadException(function (ContextElementEvent $event) {
            $event->getElement()->skip(); // Useless at that point - because an exception occured, the element could no be loaded.
            $event->setException(null);
        });
        $runScenario();
        $this->assertEquals(['foo', 'baz'], array_values($this->loader->getFlushedElements()));
    }

    public function testLoadExceptionIsCaughtAndSkippedAndETLIsStopped()
    {
        $runScenario = $this->scenario;
        $runner = $this->runner;
        $runner->onLoadException(function (ContextElementEvent $event) {
            $event->getElement()->stop();
            $event->setException(null);
        });
        $runScenario();
        $this->assertEquals(['foo'], array_values($this->loader->getFlushedElements()));
    }

    public function testLoadExceptionIsCaughtAndSkippedAndETLIsStoppedAndNothingIsFlushed()
    {
        $runScenario = $this->scenario;
        $runner = $this->runner;
        $this->loader->setFlushEvery(5);
        $runner->onLoadException(function (ContextElementEvent $event) {
            $event->getElement()->stop(false);
            $event->setException(null);
        });
        $runScenario();
        $this->assertEquals([], array_values($this->loader->getFlushedElements()));
    }


}
