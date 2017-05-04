<?php

namespace BenTools\ETL\Event;

use BenTools\ETL\Exception\ExtractionFailedException;
use Throwable;

class ExtractExceptionEvent extends ETLEvent
{

    private $exception;

    /**
     * @var null
     */
    private $key;

    /**
     * @var null
     */
    private $value;

    /**
     * ExtractExceptionEvent constructor.
     *
     * @param ExtractionFailedException $exception
     * @param null                      $key
     * @param null                      $value
     * @param string                    $name
     */
    public function __construct(
        ExtractionFailedException $exception,
        $key = null,
        $value = null,
        string $name = ETLEvents::ON_EXTRACT_EXCEPTION
    ) {
    
        parent::__construct($name);
        $this->exception = $exception;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param bool $shouldIgnore
     */
    public function ignore(bool $shouldIgnore)
    {
        $this->exception->ignore($shouldIgnore);
    }

    /**
     * @param bool $shouldStop
     * @param bool $flush
     */
    public function stop(bool $shouldStop, bool $flush = true)
    {
        $this->exception->stop($shouldStop, $flush);
    }

    /**
     * @return bool
     */
    public function shouldIgnore()
    {
        return $this->exception->shouldIgnore();
    }

    /**
     * @return bool
     */
    public function shouldStop()
    {
        return $this->exception->shouldStop();
    }

    /**
     * @return bool
     */
    public function shouldFlush()
    {
        return $this->exception->shouldFlush();
    }

    /**
     * @return ExtractionFailedException
     */
    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * @inheritdoc
     */
    public function setException(Throwable $exception = null)
    {
        throw new \LogicException(sprintf('Calling %s is not allowed.', __METHOD__));
    }

    /**
     * @inheritdoc
     */
    public function removeException()
    {
        throw new \LogicException(sprintf('Calling %s is not allowed.', __METHOD__));
    }
}
