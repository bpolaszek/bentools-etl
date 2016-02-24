<?php
namespace BenTools\ETL\Extractor;

use BenTools\ETL\Interfaces\ContextInterface;
use BenTools\ETL\Interfaces\ExtractorInterface;
use Iterator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IteratorExtractor implements ExtractorInterface, \IteratorAggregate, \Countable {

    /**
     * @var Iterator
     */
    protected $iterator;

    /**
     * @var bool
     */
    protected $init = false;

    /**
     * @param Iterator $iterator
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Iterator $iterator) {
        $this->setIterator($iterator);
    }

    /**
     * extract data to be transformed
     *
     * @return array
     */
    public function extract(ContextInterface $context) {
        if ($this->init === false) {
            $this->iterator->rewind();
            $this->init = true;
        }
        $data = $this->iterator->current();
        $this->iterator->next();
        return $data;
    }

    /**
     * @param Iterator $iterator
     * @return $this - Provides Fluent Interface
     */
    public function setIterator(Iterator $iterator) {
        $this->iterator = $iterator;
        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Iterator An instance of an object implementing <b>Iterator</b> or
     *       <b>Iterator</b>
     */
    public function getIterator() {
        return $this->iterator;
    }

    /**
     * @return int
     */
    public function count() {
        return count($this->getIterator());
    }
}