<?php

namespace BenTools\ETL\Tests;

class TestSuite
{

    const DATA_DIR = __DIR__ . '/../data';

    /**
     * @param $fileName
     * @return string
     */
    public static function getDataFile($fileName)
    {
        return sprintf('%s/%s', self::DATA_DIR, $fileName);
    }
}
