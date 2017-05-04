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

class ETLRunnerExtractExceptionsTest extends TestCase
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
        $this->loader = new FlushableLoaderExample();
        $this->scenario = function () {
            $data = [
                'foo',
                'bar',
                'baz',
            ];

            $extract = new class extends IncrementorExtractor
            {
                public function __invoke($key, $value): ContextElementInterface
                {
                    if ('bar' === $value) {
                        throw new \RuntimeException();
                    }
                    return parent::__invoke($key, $value);
                }

            };
            $transform = function (ContextElementInterface $element) {
                return $element;
            };
            $load = $this->loader;
            $run = $this->runner;
            $run($data, $extract, $transform, $load);
        };
    }

    /**
     * @expectedException \BenTools\ETL\Exception\ExtractionFailedException
     */
    public function testExtractExceptionIsThrown()
    {
        $runScenario = $this->scenario;
        $runScenario();
    }

    public function testExtractExceptionIsCaughtAndIgnored()
    {
        $runScenario = $this->scenario;
        $runner = $this->runner;
        $runner->onExtractException(function (ExtractExceptionEvent $event) {
            $event->ignore(true);
        });
        $runScenario();
        $this->assertEquals(['foo', 'baz'], array_values($this->loader->getFlushedElements()));
    }

    public function testExtractExceptionIsCaughtAndETLIsStopped()
    {
        $runScenario = $this->scenario;
        $runner = $this->runner;
        $runner->onExtractException(function (ExtractExceptionEvent $event) {
            $event->ignore(true);
            $event->stop(true);
        });
        $runScenario();
        $this->assertEquals(['foo'], array_values($this->loader->getFlushedElements()));
    }

    public function testExtractExceptionIsCaughtAndETLIsStoppedAndNothingIsFlushed()
    {
        $runScenario = $this->scenario;
        $runner = $this->runner;
        $this->loader->setFlushEvery(5);
        $runner->onExtractException(function (ExtractExceptionEvent $event) {
            $event->ignore(true);
            $event->stop(true, false);
        });
        $runScenario();
        $this->assertEquals([], array_values($this->loader->getFlushedElements()));
    }


}
