<?php

declare(strict_types=1);

namespace BenTools\ETL\Iterator;

use Evenement\EventEmitterTrait;
use React\EventLoop\Loop;
use React\Stream\ReadableStreamInterface;
use React\Stream\Util;
use React\Stream\WritableStreamInterface;

/**
 * @internal
 *
 * @template T
 */
final class IteratorStream implements ReadableStreamInterface
{
    use EventEmitterTrait;

    /**
     * @var ConsumableIterator<T>
     */
    public readonly ConsumableIterator $iterator;
    public bool $paused = false;

    /**
     * @param iterable<T> $items
     */
    public function __construct(iterable $items)
    {
        $this->iterator = new ConsumableIterator($items);
        $this->resume();
    }

    public function isReadable(): bool
    {
        return !$this->iterator->isComplete();
    }

    public function pause(): void
    {
        $this->paused = true;
    }

    public function resume(): void
    {
        $this->paused = false;
        $this->process();
    }

    private function process(): void
    {
        if (!$this->iterator->isComplete()) {
            Loop::futureTick(function () {
                if (!$this->paused) {
                    $this->emit('data', [$this->iterator->consume()]);
                }
                $this->process();
            });
        } else {
            $this->emit('end');
            $this->close();
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function pipe(WritableStreamInterface $dest, array $options = []): WritableStreamInterface
    {
        Util::pipe($this, $dest, $options);

        return $dest;
    }

    public function close(): void
    {
        $this->emit('close');
    }
}
