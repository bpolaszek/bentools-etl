<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;
use SplFileObject;

final class JsonFileLoader implements LoaderInterface
{

    /**
     * @var SplFileObject
     */
    private $file;

    /**
     * @var int
     */
    private $jsonOptions = 0;

    /**
     * @var int
     */
    private $jsonDepth = 512;

    /**
     * @var array
     */
    private $data = [];

    /**
     * JsonFileLoader constructor.
     *
     * @param SplFileObject $file
     * @param int            $jsonOptions
     * @param int            $jsonDepth
     */
    public function __construct(SplFileObject $file, int $jsonOptions = 0, int $jsonDepth = 512)
    {
        $this->file = $file;
        $this->jsonOptions = $jsonOptions;
        $this->jsonDepth = $jsonDepth;
    }

    /**
     * @inheritDoc
     */
    public function load(\Generator $items, $identifier, Etl $etl): void
    {
        foreach ($items as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
    }


    /**
     * @inheritDoc
     */
    public function commit(bool $partial): void
    {
        if (true === $partial) {
            return;
        }

        if (0 === $this->file->fwrite(json_encode($this->data, $this->jsonOptions, $this->jsonDepth))) {
            throw new \RuntimeException(sprintf('Unable to write to %s', $this->file->getPathname()));
        }
    }

    /**
     * @param string $filename
     * @param int    $jsonOptions
     * @param int    $jsonDepth
     * @return JsonFileLoader
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function toFile(string $filename, int $jsonOptions = 0, int $jsonDepth = 512): self
    {
        return new self(new SplFileObject($filename, 'w'), $jsonOptions, $jsonDepth);
    }
}
