<?php

namespace BenTools\ETL\EventDispatcher\Event;

use BenTools\ETL\Etl;

final class ItemEvent extends EtlEvent
{
    /**
     * @var string
     */
    private $name;
    private $item;
    private $key;

    /**
     * ItemEvent constructor.
     *
     * @param string $name
     * @param        $item
     * @param        $key
     * @param Etl    $etl
     */
    public function __construct(string $name, $item, $key, Etl $etl)
    {
        $this->name = $name;
        $this->item = $item;
        $this->key = $key;
        parent::__construct($etl);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
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
}
