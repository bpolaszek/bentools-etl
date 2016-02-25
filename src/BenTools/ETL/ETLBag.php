<?php
namespace BenTools\ETL;

use BenTools\ETL\Interfaces\ExtractorInterface;
use BenTools\ETL\Interfaces\LoaderInterface;
use BenTools\ETL\Interfaces\TransformerInterface;
use Closure;

class ETLBag {

    /**
     * @var ExtractorInterface
     */
    protected $extractor;

    /**
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var Context
     */
    protected $context;

    protected $name = '';

    /**
     * ETLBag constructor.
     * @param ExtractorInterface $extractor
     * @param TransformerInterface $transformer
     * @param LoaderInterface $loader
     * @param Context $context
     * @param string $name
     */
    public function __construct($extractor = null, $transformer = null, $loader = null, $context = null, $name = null) {
        $this->extractor   = $extractor;
        $this->transformer = $transformer;
        $this->loader      = $loader;
        $this->context     = $context;
        $this->name        = $name;
    }

    /**
     * @return ExtractorInterface
     */
    public function getExtractor() {
        if ($this->extractor instanceof Closure) {
            $extractorFn     = $this->extractor;
            $this->extractor = $extractorFn($this->getContext());
        }
        if (!$this->extractor instanceof ExtractorInterface)
            throw new \RuntimeException("An extractor should implement ExtractorInterface.");
        return $this->extractor;
    }

    /**
     * @param ExtractorInterface|Closure $extractor
     * @return $this - Provides Fluent Interface
     */
    public function setExtractor($extractor) {
        $this->extractor = $extractor;
        return $this;
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer() {
        if ($this->transformer instanceof Closure) {
            $transformerFn     = $this->transformer;
            $this->transformer = $transformerFn($this->getContext());
        }
        if (!$this->transformer instanceof TransformerInterface)
            throw new \RuntimeException("A Transformer should implement TransformerInterface.");
        return $this->transformer;
    }

    /**
     * @param TransformerInterface|Closure $transformer
     * @return $this - Provides Fluent Interface
     */
    public function setTransformer($transformer) {
        $this->transformer = $transformer;
        return $this;
    }

    /**
     * @return LoaderInterface
     */
    public function getLoader() {
        if ($this->loader instanceof Closure) {
            $loaderFn     = $this->loader;
            $this->loader = $loaderFn($this->getContext());
        }
        if (!$this->loader instanceof LoaderInterface)
            throw new \RuntimeException("A Loader should implement LoaderInterface.");
        return $this->loader;
    }

    /**
     * @param LoaderInterface|Closure $loader
     * @return $this - Provides Fluent Interface
     */
    public function setLoader($loader) {
        $this->loader = $loader;
        return $this;
    }

    /**
     * @return Context
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * @param Context $context
     * @return $this - Provides Fluent Interface
     */
    public function setContext($context) {
        $this->context = $context;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this - Provides Fluent Interface
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

}