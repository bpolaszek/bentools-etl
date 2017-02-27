<?php

namespace BenTools\ETL\Loader;

class JsonFileLoader extends ArrayLoader implements FlushableLoaderInterface {

    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * @var int
     */
    private $jsonOptions = 0;

    /**
     * @var int
     */
    private $jsonDepth = 512;

    /**
     * JsonFileLoader constructor.
     * @param \SplFileObject $file
     * @param int $jsonOptions
     * @param int $jsonDepth
     */
    public function __construct(\SplFileObject $file, int $jsonOptions = 0, int $jsonDepth = 512) {
        $output = [];
        parent::__construct($output);
        $this->file = $file;
        $this->jsonOptions = $jsonOptions;
        $this->jsonDepth = $jsonDepth;
    }

    /**
     * @inheritDoc
     */
    public function shouldFlushAfterLoad(): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function flush(): void {
        $this->file->fwrite(json_encode($this->getArray(), $this->jsonOptions, $this->jsonDepth));
    }
}