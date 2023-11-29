<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Stubs;

use Evenement\EventEmitterTrait;
use React\Stream\WritableStreamInterface;

final class WritableStreamStub implements WritableStreamInterface
{
    use EventEmitterTrait;

    /**
     * @var list<mixed>
     */
    public array $data = [];

    public function isWritable(): bool
    {
        return true;
    }

    public function write($data): bool
    {
        $this->data[] = $data;

        return true;
    }

    public function end($data = null): void
    {
    }

    public function close(): void
    {
    }
}
