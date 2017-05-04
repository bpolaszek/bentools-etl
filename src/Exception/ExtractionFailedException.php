<?php

namespace BenTools\ETL\Exception;

class ExtractionFailedException extends \RuntimeException
{

    private $ignore = false;
    private $stop = false;
    private $flush = true;

    /**
     * @param bool $shouldIgnore
     */
    public function ignore(bool $shouldIgnore)
    {
        $this->ignore = $shouldIgnore;
    }

    /**
     * @param bool $shouldStop
     * @param bool $shouldFlush
     */
    public function stop(bool $shouldStop, $shouldFlush = true)
    {
        $this->stop = $shouldStop;
        $this->flush = $shouldFlush;
    }

    /**
     * @return bool
     */
    public function shouldIgnore()
    {
        return $this->ignore;
    }

    /**
     * @return bool
     */
    public function shouldStop()
    {
        return $this->stop;
    }

    /**
     * @return bool
     */
    public function shouldFlush()
    {
        return $this->flush;
    }
}
