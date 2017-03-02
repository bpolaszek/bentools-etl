<?php

namespace BenTools\ETL\Loader;

class DebugLoader extends ArrayLoader implements FlushableLoaderInterface
{
    /**
     * @var callable
     */
    private $debugFn;

    /**
     * @inheritDoc
     */
    public function __construct(array $array = [], $debugFn = 'var_dump')
    {
        parent::__construct($array);
        $this->debugFn = $debugFn;
    }

    /**
     * @inheritDoc
     */
    public function shouldFlushAfterLoad(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function flush(): void
    {
        $debugFn = $this->debugFn;
        if (!is_callable($debugFn)) {
            throw new \RuntimeException("The debug function is not callable");
        }
        $debugFn($this->array);
    }

}