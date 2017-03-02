<?php

namespace BenTools\ETL\Context;

interface ContextElementInterface
{

    /**
     * @param int|string $id the identifier value of current data
     */
    public function setId($id): void;

    /**
     * @return int|string
     */
    public function getId();

    /**
     * @param mixed $data
     */
    public function setData($data): void;

    /**
     * @return mixed
     */
    public function getData();

    /**
     * This method may be called if this element should not be transformed or loaded.
     */
    public function skip(): void;

    /**
     * This method may be called if no other element should be transformed or loaded.
     *
     * @param bool $flush
     */
    public function stop(bool $flush = true): void;

    /**
     * This method may be called to request the loader to flush immediately.
     *
     * @param bool $flush
     */
    public function flush(): void;

    /**
     * Indicates if the ETL should skip this row.
     *
     * @return boolean
     */
    public function shouldSkip(): bool;

    /**
     * Indicates if the ETL should stop and go flushing.
     *
     * @return boolean
     */
    public function shouldStop(): bool;

    /**
     * Indicates if the ETL should flush.
     *
     * @return boolean
     */
    public function shouldFlush(): bool;
}
