<?php

namespace BenTools\ETL\Context;

class ContextElement implements ContextElementInterface
{

    private $id;
    private $data;
    private $skip = false;
    private $stop = false;
    private $flush = false;

    /**
     * ContextElement constructor.
     *
     * @param $id
     * @param $data
     */
    public function __construct($id = null, $data = null)
    {
        $this->id   = $id;
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function skip(): void
    {
        $this->skip = true;
    }

    /**
     * @inheritDoc
     */
    public function stop(bool $flush = true): void
    {
        $this->stop = true;
        $this->flush = $flush;
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        $this->flush = true;
    }

    /**
     * @inheritDoc
     */
    public function shouldSkip(): bool
    {
        return $this->skip;
    }

    /**
     * @inheritDoc
     */
    public function shouldStop(): bool
    {
        return $this->stop;
    }

    /**
     * @inheritDoc
     */
    public function shouldFlush(): bool
    {
        return $this->flush;
    }
}
