<?php

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\Etl;
use BenTools\ETL\EventDispatcher\EtlEvents;

final class ItemExceptionEvent extends EtlEvent
{
    /**
     * @var string
     */
    private $name;

    private $item;
    private $key;

    /**
     * @var \Throwable
     */
    private $exception;

    /**
     * @var bool
     */
    private $shouldBeThrown = true;


    /**
     * ItemEvent constructor.
     *
     * @param        $item
     * @param        $key
     * @param Etl    $etl
     */
    public function __construct(string $name, $item, $key, Etl $etl, \Throwable $exception)
    {
        $this->name = $name;
        $this->item = $item;
        $this->key = $key;
        $this->exception = $exception;
        parent::__construct($etl);
    }

    /**
     * @return \Throwable
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }

    /**
     * @return bool
     */
    public function shouldThrowException(): bool
    {
        return $this->shouldBeThrown;
    }

    /**
     * Exception should not be thrown.
     * Implicitely skips the current item.
     */
    public function ignoreException(): void
    {
        $this->shouldBeThrown = false;
        $this->etl->skipCurrentItem();
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }
}
