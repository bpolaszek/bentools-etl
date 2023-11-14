<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Stubs;

use function file_exists;
use function file_put_contents;
use function min;
use function stream_wrapper_register;
use function stream_wrapper_restore;
use function stream_wrapper_unregister;
use function strlen;
use function substr;

/**
 * Inspired by @KEINOS.
 *
 * @see https://github.com/KEINOS/Practice_PHPUnit-test-of-STDIN
 */
final class STDINStub
{
    private string $bufferFilename;
    private int $index;
    private int $length;
    private string $data = '';
    public mixed $context;

    public function __construct()
    {
        $this->bufferFilename = sys_get_temp_dir().DIRECTORY_SEPARATOR.'php_input.txt';
        $this->index = 0;
        if (file_exists($this->bufferFilename)) {
            $this->data = file_get_contents($this->bufferFilename);
        }
        $this->length = strlen($this->data);
    }

    public function stream_open(): true
    {
        return true;
    }

    public function url_stat(): false
    {
        return false;
    }

    public function stream_close(): void
    {
    }

    public function stream_stat(): false
    {
        return false;
    }

    public function stream_flush(): true
    {
        return true;
    }

    public function stream_read(int $count): string
    {
        $length = min($count, $this->length - $this->index);
        $data = substr($this->data, $this->index);
        $this->index += $length;

        return $data;
    }

    public function stream_eof(): bool
    {
        return $this->index >= $this->length;
    }

    public function stream_write(string $data): false|int
    {
        return file_put_contents($this->bufferFilename, $data);
    }

    public static function emulate(string $stdInContent, callable $beforeRestore): mixed
    {
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', __CLASS__);
        file_put_contents('php://stdin', $stdInContent);
        $result = $beforeRestore();
        stream_wrapper_restore('php');

        return $result;
    }
}
