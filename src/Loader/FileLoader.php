<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;
use BenTools\ETL\Exception\UnexpectedTypeException;
use SplFileObject;

final class FileLoader implements LoaderInterface
{
    public const NO_EOL = '';

    /**
     * @var SplFileObject
     */
    protected $file;

    /**
     * @var string
     */
    private $eol;

    /**
     * FileLoader constructor.
     *
     * @param string|SplFileObject $file
     * @param string               $eol
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function __construct($file = null, array $options = [])
    {
        self::factory(\array_replace($options, ['file' => $file]), $this);
    }

    /**
     * @inheritDoc
     */
    public function init($options = []): void
    {
        if (\func_num_args() > 0) {
            if (!\is_array($file = \func_get_arg(0))) {
                self::factory(['file' => $file], $this);
            } else {
                self::factory($options, $this);
            }
        }
        unset($this->file);
        self::factory($options, $this);
    }

    /**
     * @inheritDoc
     */
    public function load(\Generator $items, $key, Etl $etl): void
    {
        UnexpectedTypeException::throwIfNot($this->file, SplFileObject::class);
        foreach ($items as $item) {
            $this->file->fwrite($item.$this->eol);
        }
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
    }

    /**
     * @param string|SplFileObject $file
     * @param array                $options
     * @return FileLoader
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function toFile($file, array $options): self
    {
        return self::factory(\array_replace($options, ['file' => $file]));
    }

    /**
     * @param array     $options
     * @param self|null $that
     * @return FileLoader
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function factory(array $options = [], self $that = null): self
    {
        $that = $that ?? new self;

        $file = $options['file'] ?? $that->file ?? null;
        if ($file instanceof SplFileObject) {
            $that->file = $file;
        } elseif (is_string($file)) {
            $that->file = new SplFileObject($file, 'w');
        }
        UnexpectedTypeException::throwIfNot($that->file, SplFileObject::class, true);

        $that->eol = $options['eol'] ?? $that->eol ?? \PHP_EOL;

        return $that;
    }
}
