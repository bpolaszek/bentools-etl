<?php

use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Extractor\KeyValueExtractor;
use BenTools\ETL\Loader\FlushableLoaderInterface;
use PHPUnit\Framework\TestCase;

use BenTools\ETL\Runner\Runner;

class RunnerTest extends TestCase {

    public function testSimpleETL() {
        $output      = [];
        $items       = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $extractor   = new KeyValueExtractor();
        $transformer = function (ContextElementInterface $element) {
            $data = $element->getExtractedData();
            $element->setTransformedData(join('|', [
                $data['country_code'],
                $data['periods'][0]['effective_from'],
                $data['periods'][0]['rates']['standard'],
            ]));
        };
        $loader      = function (ContextElementInterface $element) use (&$output) {
            $output[$element->getId()] = $element->getTransformedData();
        };

        $run = new Runner();
        $run($items, $extractor, $transformer, $loader);
        $this->assertCount(count($output), $items);
        $this->assertSame('HU|0000-01-01|27', $output[2]);
        return $output;
    }

    /**
     * @depends testSimpleETL
     */
    public function testETLWithFlushableLoader($input) {
        $items       = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $extractor   = new KeyValueExtractor();
        $transformer = function (ContextElementInterface $element) {
            $data = $element->getExtractedData();
            $element->setTransformedData(join('|', [
                $data['country_code'],
                $data['periods'][0]['effective_from'],
                $data['periods'][0]['rates']['standard'],
            ]));
        };
        $flushEvery  = 5;
        $loader      = new class($flushEvery) implements FlushableLoaderInterface {

            private $tmp     = [];
            private $output  = [];
            private $counter = 0;
            private $nbFlush = 0;
            private $flushEvery;

            public function __construct(int $flushEvery) {
                $this->flushEvery = $flushEvery;
            }

            public function shouldFlushAfterLoad(): bool {
                return 0 === ($this->counter % $this->flushEvery);
            }

            public function flush(): void {
                $this->output = array_replace($this->output, $this->tmp);
                $this->tmp    = [];
                $this->nbFlush++;
            }

            public function getOutput() {
                return $this->output;
            }

            public function getNbFlush() {
                return $this->nbFlush;
            }

            public function __invoke(ContextElementInterface $element): void {
                $this->tmp[$element->getId()] = $element->getTransformedData();
                $this->counter++;
            }
        };
        $run         = new Runner();
        $run($items, $extractor, $transformer, $loader);


        $this->assertCount(count($loader->getOutput()), $items);
        $this->assertSame('HU|0000-01-01|27', $loader->getOutput()[2]);
        $this->assertSame($loader->getOutput(), $input);
        $this->assertSame($loader->getNbFlush(), (int) ceil(count($items) / $flushEvery));
    }

    /**
     * Skip 1 item
     * @depends testSimpleETL
     */
    public function testSkip($input) {
        $items       = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $extractor   = new KeyValueExtractor();
        $transformer = function (ContextElementInterface $element) {
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
        $flushEvery  = 5;
        $loader      = new class($flushEvery) implements FlushableLoaderInterface {

            private $tmp     = [];
            private $output  = [];
            private $counter = 0;
            private $nbFlush = 0;
            private $flushEvery;

            public function __construct(int $flushEvery) {
                $this->flushEvery = $flushEvery;
            }

            public function flush(): void {
                $this->output = array_replace($this->output, $this->tmp);
                $this->tmp    = [];
                $this->nbFlush++;
            }

            public function shouldFlushAfterLoad(): bool {
                return 0 === ($this->counter % $this->flushEvery);
            }

            public function getOutput() {
                return $this->output;
            }

            public function getNbFlush() {
                return $this->nbFlush;
            }

            public function __invoke(ContextElementInterface $element): void {
                $this->tmp[$element->getId()] = $element->getTransformedData();
                $this->counter++;
            }
        };
        $run         = new Runner();
        $run($items, $extractor, $transformer, $loader);


        $this->assertSame(count($loader->getOutput()), count($items) - 1);
        $this->assertSame('HU|0000-01-01|27', $loader->getOutput()[2]);
        $this->assertSame($loader->getNbFlush(), (int) ceil((count($items) - 1) / $flushEvery));
    }

    /**
     * Abort and flush
     * @depends testSimpleETL
     */
    public function testAbortAndFlush($input) {
        $items       = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $extractor   = new KeyValueExtractor();
        $transformer = function (ContextElementInterface $element) {
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
        $flushEvery  = 5;
        $loader      = new class($flushEvery) implements FlushableLoaderInterface {

            private $tmp     = [];
            private $output  = [];
            private $counter = 0;
            private $nbFlush = 0;
            private $flushEvery;

            public function __construct(int $flushEvery) {
                $this->flushEvery = $flushEvery;
            }

            public function flush(): void {
                $this->output = array_replace($this->output, $this->tmp);
                $this->tmp    = [];
                $this->nbFlush++;
            }
            public function shouldFlushAfterLoad(): bool {
                return 0 === ($this->counter % $this->flushEvery);
            }

            public function getOutput() {
                return $this->output;
            }

            public function getNbFlush() {
                return $this->nbFlush;
            }

            public function __invoke(ContextElementInterface $element): void {
                $this->tmp[$element->getId()] = $element->getTransformedData();
                $this->counter++;
            }
        };
        $run         = new Runner();
        $run($items, $extractor, $transformer, $loader);

        $this->assertCount(6, $loader->getOutput());
    }

    /**
     * Abort and flush
     * @depends testSimpleETL
     */
    public function testAbortAndDoNotFlush($input) {
        $items       = json_decode(file_get_contents(__DIR__ . '/data/vat.json'), true)['rates'];
        $extractor   = new KeyValueExtractor();
        $transformer = function (ContextElementInterface $element) {
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
        $flushEvery  = 5;
        $loader      = new class($flushEvery) implements FlushableLoaderInterface {

            private $tmp     = [];
            private $output  = [];
            private $counter = 0;
            private $nbFlush = 0;
            private $flushEvery;

            public function __construct(int $flushEvery) {
                $this->flushEvery = $flushEvery;
            }

            public function flush(): void {
                $this->output = array_replace($this->output, $this->tmp);
                $this->tmp    = [];
                $this->nbFlush++;
            }

            public function shouldFlushAfterLoad(): bool {
                return 0 === ($this->counter % $this->flushEvery);
            }

            public function getOutput() {
                return $this->output;
            }

            public function getNbFlush() {
                return $this->nbFlush;
            }

            public function __invoke(ContextElementInterface $element): void {
                $this->tmp[$element->getId()] = $element->getTransformedData();
                $this->counter++;
            }
        };
        $run         = new Runner();
        $run($items, $extractor, $transformer, $loader);

        $this->assertCount(5, $loader->getOutput());
    }

}
