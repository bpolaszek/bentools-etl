<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Stubs;

use function fopen;
use function stream_bucket_append;
use function stream_bucket_make_writeable;
use function stream_filter_append;
use function stream_filter_register;

final class STDOUTStub
{
    public string $filtername = 'intercept';
    public ?array $params = null; // @phpstan-ignore-line
    private static string $storage = '';

    // @phpstan-ignore-next-line
    public function filter($in, $out, &$consumed, bool $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            self::$storage .= $bucket->data;
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

    public static function read(): string
    {
        return self::$storage;
    }

    public static function emulate(callable $beforeRestore, string $filename = 'php://stdout'): string
    {
        stream_filter_register('intercept', __CLASS__);
        $stdout = fopen($filename, 'wb+');
        $filter = stream_filter_append($stdout, 'intercept');
        $beforeRestore($stdout);
        $result = self::$storage;

        self::$storage = '';

        return $result;
    }
}
