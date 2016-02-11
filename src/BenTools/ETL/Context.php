<?php
namespace BenTools\ETL;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Context extends \Knp\ETL\Context\Context implements LoggerAwareInterface, \ArrayAccess {

    use LoggerAwareTrait;

    /**
     * @var ETLBag
     */
    protected $currentETL;
    protected $shouldSkip  =   false;
    protected $shouldBreak =   false;
    protected $shouldHalt  =   false;
    protected $shouldFlush =   false;
    protected $identifier;
    protected $extractorIdentifierCallable;
    protected $transformerIdentifierCallable;
    protected $container   = [];

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger) {
        $this->setLogger($logger);
    }

    /**
     * @return ETLBag
     */
    public function getCurrentETL() {
        return $this->currentETL;
    }

    /**
     * @param ETLBag $currentETL
     * @return $this - Provides Fluent Interface
     */
    public function setCurrentETL(ETLBag $currentETL) {
        $this->currentETL = $currentETL;
        return $this;
    }

    /**
     * Indicates if the ETL should skip this row.
     * @return boolean
     */
    public function shouldSkip($value = null) {
        if (func_num_args() > 0)
            $this->shouldSkip   =   $value;
        return $this->shouldSkip;
    }

    /**
     * Indicates if the ETL should stop and go flushing.
     * @return boolean
     */
    public function shouldBreak($value = null) {
        if (func_num_args() > 0)
            $this->shouldBreak  =   $value;
        return $this->shouldBreak;
    }

    /**
     * Indicates if the ETL should stop and not flush anything.
     * @return boolean
     */
    public function shouldHalt($value = null) {
        if (func_num_args() > 0)
            $this->shouldHalt   =   $value;
        return $this->shouldHalt;
    }

    /**
     * Indicates if the ETL should flush.
     * @return boolean
     */
    public function shouldFlush($value = null) {
        if (func_num_args() > 0)
            $this->shouldFlush   =   $value;
        return $this->shouldFlush;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * @return callable
     */
    public function getExtractorIdentifierCallable() {
        return $this->extractorIdentifierCallable;
    }

    /**
     * @param callable $extractorIdentifierCallable
     * @return $this - Provides Fluent Interface
     */
    public function setExtractorIdentifierCallable(callable $extractorIdentifierCallable) {
        $this->extractorIdentifierCallable = $extractorIdentifierCallable;
        return $this;
    }

    /**
     * @return callable
     */
    public function getTransformerIdentifierCallable() {
        return $this->transformerIdentifierCallable;
    }

    /**
     * @param callable $transformerIdentifierCallable
     * @return $this - Provides Fluent Interface
     */
    public function setTransformerIdentifierCallable(callable $transformerIdentifierCallable) {
        $this->transformerIdentifierCallable = $transformerIdentifierCallable;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     * @return $this - Provides Fluent Interface
     */
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setExtractedData($data) {
        parent::setExtractedData($data);
        if ($data && is_callable($this->getExtractorIdentifierCallable()))
            $this->setIdentifier(call_user_func($this->getExtractorIdentifierCallable(), $data));
    }

    /**
     * @inheritDoc
     */
    public function setTransformedData($data) {
        parent::setTransformedData($data);
        if ($data && is_callable($this->getTransformedData()))
            $this->setIdentifier(call_user_func($this->getTransformerIdentifierCallable(), $data));
    }

    /**
     * @return array|\ArrayAccess (or container)
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * @param array|\ArrayAccess $container
     * @return $this - Provides Fluent Interface
     */
    public function setContainer($container) {
        $this->container = $container;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset) {
        return (isset($this->container[$offset])) ? $this->container[$offset] : null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value) {
        $this->container[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset) {
        unset ($this->container[$offset]);
    }

}