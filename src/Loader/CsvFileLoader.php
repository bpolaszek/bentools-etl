<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;
use BenTools\ETL\Exception\UnexpectedTypeException;
use SplFileObject;

final class CsvFileLoader implements LoaderInterface
{
    /**
     * @var SplFileObject
     */
    private $file;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $enclosure;

    /**
     * @var string
     */
    private $escape;

    /**
     * @var array
     */
    private $keys;

    /**
     * CsvFileLoader constructor.
     *
     * @param string|SplFileObject  $file
     * @param array $options
     */
    public function __construct($file = null, array $options = [])
    {
        self::factory(\array_replace($options, ['file' => $file]), $this);
    }

    /**
     * @inheritDoc
     */
    public function load(\Generator $generator, $key, Etl $etl): void
    {
        UnexpectedTypeException::throwIfNot($this->file, SplFileObject::class);
        foreach ($generator as $row) {
            $this->file->fputcsv($row, $this->delimiter, $this->enclosure, $this->escape);
        }
    }

    /**
     * @inheritDoc
     */
    public function init($options = null): void
    {
        if (\func_num_args() > 0) {
            if (!\is_array($file = \func_get_arg(0))) {
                self::factory(['file' => $file], $this);
            } else {
                self::factory($options, $this);
            }
        }

        if (!empty($this->keys)) {
            $this->file->fputcsv($this->keys, $this->delimiter, $this->enclosure, $this->escape);
        }
    }

    /**
     * @inheritDoc
     */
    public function commit(bool $partial): void
    {
    }

    /**
     * @inheritDoc
     */
    public function rollback(): void
    {
    }

    /**
     * @param       $file
     * @param array $options
     * @return FileLoader
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function toFile($file, array $options): self
    {
        return self::factory(\array_replace($options, ['file' => $file]));
    }

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

        $that->delimiter = $options['delimiter'] ?? $that->delimiter ?? ',';
        $that->enclosure = $options['enclosure'] ?? $that->enclosure ?? '"';
        $that->escape = $options['escape'] ?? $that->escape ?? '\\';
        $that->keys = $options['keys'] ?? $that->keys ?? [];

        return $that;
    }
}
