<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Etl;
use Safe\Exceptions\JsonException;

final class JsonExtractor implements ExtractorInterface
{
    /**
     * @inheritDoc
     */
    public function extract($json, Etl $etl): iterable
    {
        if (\is_array($json)) {
            return $json;
        }

        try {
            $json = \Safe\json_decode($json, true);
            return $json;
        } catch (JsonException $e) {
            // Is it a file?
            if (\strlen($json) < 3000 && \file_exists($json)) {
                return $this->extract(\Safe\file_get_contents($json), $etl);
            }

            throw $e;
        }
    }
}
