<?php

declare(strict_types=1);

namespace BenTools\ETL;

use BenTools\ETL\Exception\SkipRequest;
use BenTools\ETL\Exception\StopRequest;
use BenTools\ETL\Internal\ClonableTrait;
use Closure;
use DateTimeImmutable;
use SplObjectStorage;

final class EtlState
{
    use ClonableTrait;

    /**
     * @internal
     *
     * @var SplObjectStorage<Closure, Closure>
     */
    public SplObjectStorage $nextTickCallbacks;

    private int $nbLoadedItemsSinceLastFlush = 0;
    private bool $earlyFlush = false;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly EtlConfiguration $options = new EtlConfiguration(),
        public readonly mixed $source = null,
        public readonly mixed $destination = null,
        public array $context = [],
        public readonly mixed $currentItemKey = null,
        public readonly int $currentItemIndex = -1,
        public readonly int $nbExtractedItems = 0,
        public readonly int $nbLoadedItems = 0,
        public readonly ?int $nbTotalItems = null,
        public readonly DateTimeImmutable $startedAt = new DateTimeImmutable(),
        public readonly ?DateTimeImmutable $endedAt = null,
        public readonly mixed $output = null,
    ) {
        $this->nextTickCallbacks ??= new SplObjectStorage();
    }

    public function nextTick(callable $callback): void
    {
        $this->nextTickCallbacks->attach(static fn (EtlState $state) => $callback($state));
    }

    /**
     * Flush after current item.
     */
    public function flush(): void
    {
        $this->earlyFlush = true;
    }

    /**
     * Skip current item.
     */
    public function skip(): void
    {
        throw new SkipRequest();
    }

    /**
     * Stop after current item.
     */
    public function stop(): void
    {
        throw new StopRequest();
    }

    public function getDuration(): float
    {
        $endedAt = $this->endedAt ?? new DateTimeImmutable();

        return (float) ($endedAt->format('U.u') - $this->startedAt->format('U.u'));
    }

    /**
     * @internal
     */
    public function shouldFlush(): bool
    {
        return match (true) {
            $this->earlyFlush => true,
            INF === $this->options->flushFrequency => false,
            0 === $this->nbLoadedItemsSinceLastFlush => false,
            0 === ($this->nbLoadedItemsSinceLastFlush % $this->options->flushFrequency) => true,
            default => false,
        };
    }

    /**
     * @internal
     */
    public function withUpdatedItemKey(mixed $key): self
    {
        return $this->cloneWith([
            'currentItemKey' => $key,
            'currentItemIndex' => $this->currentItemIndex + 1,
            'nbExtractedItems' => $this->nbExtractedItems + 1,
        ]);
    }

    /**
     * @internal
     */
    public function withIncrementedNbLoadedItems(): self
    {
        return $this->cloneWith([
            'nbLoadedItems' => $this->nbLoadedItems + 1,
            'nbLoadedItemsSinceLastFlush' => $this->nbLoadedItemsSinceLastFlush + 1,
        ]);
    }

    /**
     * @internal
     */
    public function withNbTotalItems(?int $nbTotalItems): self
    {
        return $this->cloneWith(['nbTotalItems' => $nbTotalItems]);
    }

    /**
     * @internal
     */
    public function withOutput(mixed $output): self
    {
        return $this->cloneWith(['output' => $output]);
    }

    /**
     * @internal
     */
    public function withClearedFlush(): self
    {
        return $this->cloneWith([
            'earlyFlush' => false,
            'nbLoadedItemsSinceLastFlush' => 0,
        ]);
    }
}
