<?php

namespace BenTools\ETL\Extractor;

use BenTools\ETL\Etl;
use function Safe\file_get_contents;

final class FileExtractor implements ExtractorInterface
{
    /**
     * @var ExtractorInterface
     */
    private $contentExtractor;

    /**
     * FileExtractor constructor.
     * @param ExtractorInterface $contentExtractor
     */
    public function __construct(ExtractorInterface $contentExtractor)
    {
        $this->contentExtractor = $contentExtractor;
    }

    /**
     * @inheritDoc
     */
    public function extract(/*string */$filename, Etl $etl): iterable
    {
        return $this->contentExtractor->extract(file_get_contents($filename), $etl);
    }
}
