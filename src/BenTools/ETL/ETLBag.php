<?php
namespace BenTools\ETL;

use Knp\ETL\ExtractorInterface;
use Knp\ETL\LoaderInterface;
use Knp\ETL\TransformerInterface;

class ETLBag {

    /**
     * @var ExtractorInterface
     */
    protected   $extractor;

    /**
     * @var TransformerInterface
     */
    protected   $transformer;

    /**
     * @var LoaderInterface
     */
    protected   $loader;

    /**
     * @var Context
     */
    protected   $context;

    protected   $name = '';

    /**
     * @param ExtractorInterface   $extractor
     * @param TransformerInterface $transformer
     * @param LoaderInterface      $loader
     * @param Context              $context
     */
    public function __construct(ExtractorInterface $extractor, TransformerInterface $transformer, LoaderInterface $loader, Context $context) {
        $this->extractor    =   $extractor;
        $this->transformer  =   $transformer;
        $this->loader       =   $loader;
        $this->context      =   $context;
    }

    /**
     * @return ExtractorInterface
     */
    public function getExtractor() {
        return $this->extractor;
    }

    /**
     * @param ExtractorInterface $extractor
     * @return $this - Provides Fluent Interface
     */
    public function setExtractor(ExtractorInterface $extractor) {
        $this->extractor = $extractor;
        return $this;
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer() {
        return $this->transformer;
    }

    /**
     * @param TransformerInterface $transformer
     * @return $this - Provides Fluent Interface
     */
    public function setTransformer(TransformerInterface $transformer) {
        $this->transformer = $transformer;
        return $this;
    }

    /**
     * @return LoaderInterface
     */
    public function getLoader() {
        return $this->loader;
    }

    /**
     * @param LoaderInterface $loader
     * @return $this - Provides Fluent Interface
     */
    public function setLoader(LoaderInterface $loader) {
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
    public function setContext(Context $context) {
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