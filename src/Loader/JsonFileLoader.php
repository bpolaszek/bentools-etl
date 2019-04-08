<?php

namespace BenTools\ETL\Loader;

use BenTools\ETL\Etl;
use BenTools\ETL\Exception\UnexpectedTypeException;
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
    private $jsonOptions;

    /**
     * @var int
     */
    private $jsonDepth;

    /**
     * @var array
     */
    private $data = [];

    /**
     * JsonFileLoader constructor.
     *
     * @param string|SplFileObject $file
     * @param array                $options
     * @throws \InvalidArgumentException
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
        $this->data = [];
    }


    /**
     * @inheritDoc
     */
    public function load(\Generator $items, $identifier, Etl $etl): void
    {
        UnexpectedTypeException::throwIfNot($this->file, SplFileObject::class);
        foreach ($items as $key => $value) {
            $this->data[$key] = $value;
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
        if (true === $partial) {
            return;
        }

        if (0 === $this->file->fwrite(json_encode($this->data, $this->jsonOptions, $this->jsonDepth))) {
            throw new \RuntimeException(sprintf('Unable to write to %s', $this->file->getPathname()));
        }
    }

    /**
     * @param string|SplFileObject $file
     * @param int                  $jsonOptions
     * @param int                  $jsonDepth
     * @return JsonFileLoader
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function toFile($file, int $jsonOptions = 0, int $jsonDepth = 512): self
    {
        return self::factory(
            [
                'file'         => $file,
                'json_options' => $jsonOptions,
                'json_depth'   => $jsonDepth,
            ]
        );
    }

    /**
     * @param array     $options
     * @param self|null $that
     * @return JsonFileLoader
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public static function factory(array $options = [], self $that = null): self
    {
        $that = $that ?? new self;

        $that->jsonOptions = $options['json_options'] ?? $that->jsonOptions ?? 0;
        $that->jsonDepth = $options['json_depth'] ?? $that->jsonDepth ?? 512;

        $file = $options['file'] ?? $that->file ?? null;
        if ($file instanceof SplFileObject) {
            $that->file = $file;
        } elseif (is_string($file)) {
            $that->file = new SplFileObject($file, 'w');
        }
        UnexpectedTypeException::throwIfNot($that->file, SplFileObject::class, true);

        return $that;
    }
}
