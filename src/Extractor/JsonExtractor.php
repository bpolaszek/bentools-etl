<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Etl;
use BenTools\ETL\Exception\UnexpectedTypeException;
use Safe\Exceptions\JsonException;

final class JsonExtractor implements ExtractorInterface
{
    public const EXTRACT_AUTO = 0;
    public const EXTRACT_FROM_STRING = 1;
    public const EXTRACT_FROM_FILE = 2;
    public const EXTRACT_FROM_ARRAY = 3;

    private $type;

    /**
     * JsonExtractor constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->type = $options['type'] ?? self::EXTRACT_AUTO;
    }

    /**
     * @param $json
     * @return iterable
     * @throws UnexpectedTypeException
     */
    private function extractFromArray($json): iterable
    {
        UnexpectedTypeException::throwIfNot($json, 'array');

        return $json;
    }

    /**
     * @param $json
     * @return iterable
     * @throws UnexpectedTypeException
     */
    private function extractFromString($json): iterable
    {
        UnexpectedTypeException::throwIfNot($json, 'string');

        return \Safe\json_decode($json, true);
    }

    /**
     * @param $json
     * @return iterable
     * @throws UnexpectedTypeException
     */
    private function extractFromFile($file): iterable
    {
        if ($file instanceof \SplFileInfo) {
            $file = $file->getPathname();
        }

        UnexpectedTypeException::throwIfNot($file, 'string');

        if (!\is_readable($file)) {
            throw new \InvalidArgumentException(sprintf('File %s is not readable.', $file));
        }

        return \Safe\json_decode(
            \Safe\file_get_contents($file),
            true
        );
    }

    private function extractAuto($json)
    {
        if (\is_array($json)) {
            return $this->extractFromArray($json);
        }

        try {
            $json = \Safe\json_decode($json, true);

            return $this->extractFromArray($json);
        } catch (JsonException $e) {
            // Is it a file?
            if (\strlen($json) < 3000 && \file_exists($json)) {
                return $this->extractFromFile($json);
            }

            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function extract($json, Etl $etl): iterable
    {
        switch ($this->type) {
            case self::EXTRACT_FROM_ARRAY:
                return $this->extractFromArray($json);
            case self::EXTRACT_FROM_FILE:
                return $this->extractFromFile($json);
            case self::EXTRACT_FROM_STRING:
                return $this->extractFromString($json);
            case self::EXTRACT_AUTO:
                return $this->extractAuto($json);
        }

        throw new \RuntimeException('Invalid type provided for '.self::class);
    }
}
