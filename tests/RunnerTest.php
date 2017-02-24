<?php

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Loader\FlushableLoaderInterface;
use PHPUnit\Framework\TestCase;

use BenTools\ETL\Runner\Runner;

class RunnerTest extends TestCase {

    public function testSimpleETL() {
        $output      = [];
        $extractor   = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $transformer = function (\BenTools\ETL\Context\ContextElementInterface $element) {
            $data = $element->getExtractedData();
            $element->setTransformedData(join('|', [
                $data['country_code'],
                $data['periods'][0]['effective_from'],
                $data['periods'][0]['rates']['standard'],
            ]));
        };
        $loader      = function (ContextElementInterface $element) use (&$output) {
            $output[$element->getIdentifier()] = $element->getTransformedData();
        };

        $run = new Runner();
        $run($extractor, $transformer, $loader);

        $this->assertCount(count($output), $extractor);
        $this->assertSame('HU|0000-01-01|27', $output[2]);
        return $output;
    }

    /**
     * @depends testSimpleETL
     */
    public function testETLWithFlushableLoader($input) {
        $extractor   = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $transformer = function (\BenTools\ETL\Context\ContextElementInterface $element) {
            $data = $element->getExtractedData();
            $element->setTransformedData(join('|', [
                $data['country_code'],
                $data['periods'][0]['effective_from'],
                $data['periods'][0]['rates']['standard'],
            ]));
        };
        $flushEvery = 5;
        $loader      = new class($flushEvery) implements FlushableLoaderInterface {

            private $tmp = [];
            private $output = [];
            private $nbLoad = 0;
            private $nbFlush = 0;
            private $flushEvery;

            public function __construct(int $flushEvery) {
                $this->flushEvery = $flushEvery;
            }

            public function flush(): void {
               $this->output = array_replace($this->output, $this->tmp);
               $this->tmp = [];
               $this->nbFlush++;
            }

            /**
             * @return array
             */
            public function getOutput() {
                return $this->output;
            }

            /**
             * @return int
             */
            public function getNbFlush() {
                return $this->nbFlush;
            }

            public function __invoke(ContextElementInterface $element): void {
                $this->tmp[$element->getIdentifier()] = $element->getTransformedData();
                $this->nbLoad++;
                if (($this->nbLoad % $this->flushEvery) === 0) {
                    $this->flush();
                }
            }
        };

        $run = new Runner();
        $run($extractor, $transformer, $loader);


        $this->assertCount(count($loader->getOutput()), $extractor);
        $this->assertSame('HU|0000-01-01|27', $loader->getOutput()[2]);
        $this->assertSame($loader->getOutput(), $input);
        $this->assertSame($loader->getNbFlush(), (int) ceil(count($extractor) / $flushEvery));
    }

    /**
     * Skip 1 item
     * @depends testSimpleETL
     */
    public function testSkip($input) {
        $extractor   = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $transformer = function (\BenTools\ETL\Context\ContextElementInterface $element) {
            $data = $element->getExtractedData();
            $element->setTransformedData(join('|', [
                $data['country_code'],
                $data['periods'][0]['effective_from'],
                $data['periods'][0]['rates']['standard'],
            ]));
            if ($data['code'] == 'PL') {
                $element->skip();
            }
        };
        $flushEvery = 5;
        $loader      = new class($flushEvery) implements FlushableLoaderInterface {

            private $tmp = [];
            private $output = [];
            private $nbLoad = 0;
            private $nbFlush = 0;
            private $flushEvery;

            public function __construct(int $flushEvery) {
                $this->flushEvery = $flushEvery;
            }

            public function flush(): void {
               $this->output = array_replace($this->output, $this->tmp);
               $this->tmp = [];
               $this->nbFlush++;
            }

            /**
             * @return array
             */
            public function getOutput() {
                return $this->output;
            }

            /**
             * @return int
             */
            public function getNbFlush() {
                return $this->nbFlush;
            }

            public function __invoke(ContextElementInterface $element): void {
                $this->tmp[$element->getIdentifier()] = $element->getTransformedData();
                $this->nbLoad++;
                if (($this->nbLoad % $this->flushEvery) === 0) {
                    $this->flush();
                }
            }
        };

        $run = new Runner();
        $run($extractor, $transformer, $loader);


        $this->assertSame(count($loader->getOutput()), count($extractor) - 1);
        $this->assertSame('HU|0000-01-01|27', $loader->getOutput()[2]);
        $this->assertSame($loader->getNbFlush(), (int) ceil((count($extractor) - 1) / $flushEvery));
    }

    /**
     * Abort and flush
     * @depends testSimpleETL
     */
    public function testAbortAndFlush($input) {
        $extractor   = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $transformer = function (\BenTools\ETL\Context\ContextElementInterface $element) {
            $data = $element->getExtractedData();
            $element->setTransformedData(join('|', [
                $data['country_code'],
                $data['periods'][0]['effective_from'],
                $data['periods'][0]['rates']['standard'],
            ]));
            if ($data['code'] == 'FR') {
                $element->stop(true);
            }
        };
        $flushEvery = 5;
        $loader      = new class($flushEvery) implements FlushableLoaderInterface {

            private $tmp = [];
            private $output = [];
            private $nbLoad = 0;
            private $nbFlush = 0;
            private $flushEvery;

            public function __construct(int $flushEvery) {
                $this->flushEvery = $flushEvery;
            }

            public function flush(): void {
               $this->output = array_replace($this->output, $this->tmp);
               $this->tmp = [];
               $this->nbFlush++;
            }

            /**
             * @return array
             */
            public function getOutput() {
                return $this->output;
            }

            /**
             * @return int
             */
            public function getNbFlush() {
                return $this->nbFlush;
            }

            public function __invoke(ContextElementInterface $element): void {
                $this->tmp[$element->getIdentifier()] = $element->getTransformedData();
                $this->nbLoad++;
                if (($this->nbLoad % $this->flushEvery) === 0) {
                    $this->flush();
                }
            }
        };

        $run = new Runner();
        $run($extractor, $transformer, $loader);

        $this->assertCount(6, $loader->getOutput());
    }

    /**
     * Abort and flush
     * @depends testSimpleETL
     */
    public function testAbortAndDoNotFlush($input) {
        $extractor   = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $transformer = function (\BenTools\ETL\Context\ContextElementInterface $element) {
            $data = $element->getExtractedData();
            $element->setTransformedData(join('|', [
                $data['country_code'],
                $data['periods'][0]['effective_from'],
                $data['periods'][0]['rates']['standard'],
            ]));
            if ($data['code'] == 'FR') {
                $element->stop(false);
            }
        };
        $flushEvery = 5;
        $loader      = new class($flushEvery) implements FlushableLoaderInterface {

            private $tmp = [];
            private $output = [];
            private $nbLoad = 0;
            private $nbFlush = 0;
            private $flushEvery;

            public function __construct(int $flushEvery) {
                $this->flushEvery = $flushEvery;
            }

            public function flush(): void {
               $this->output = array_replace($this->output, $this->tmp);
               $this->tmp = [];
               $this->nbFlush++;
            }

            /**
             * @return array
             */
            public function getOutput() {
                return $this->output;
            }

            /**
             * @return int
             */
            public function getNbFlush() {
                return $this->nbFlush;
            }

            public function __invoke(ContextElementInterface $element): void {
                $this->tmp[$element->getIdentifier()] = $element->getTransformedData();
                $this->nbLoad++;
                if (($this->nbLoad % $this->flushEvery) === 0) {
                    $this->flush();
                }
            }
        };

        $run = new Runner();
        $run($extractor, $transformer, $loader);

        $this->assertCount(5, $loader->getOutput());
    }

}
