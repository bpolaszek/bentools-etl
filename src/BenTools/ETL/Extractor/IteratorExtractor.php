<?php
namespace BenTools\ETL\Extractor;

use Iterator;
use Knp\ETL\ContextInterface;
use Knp\ETL\ExtractorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IteratorExtractor implements ExtractorInterface, \IteratorAggregate, \Countable {

    /**
     * @var Iterator
     */
    protected   $iterator;

    /**
     * @param Iterator                 $iterator
     * @param LoggerInterface          $logger
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
        $data = $this->iterator->current();
        $context->setExtractedData($data);
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