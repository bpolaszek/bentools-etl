<?php

declare(strict_types=1);

namespace Bentools\ETL;

use Bentools\ETL\Exception\SkipRequest;
use Bentools\ETL\Exception\StopRequest;
use Bentools\ETL\Internal\ClonableTrait;
use DateTimeImmutable;

final class EtlState
{
    use ClonableTrait;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly EtlConfiguration $options = new EtlConfiguration(),
        public readonly mixed $source = null,
        public readonly mixed $destination = null,
        public readonly mixed $currentItemKey = null,
        public readonly int $currentItemIndex = -1,
        public readonly int $nbExtractedItems = 0,
        public readonly int $nbLoadedItems = 0,
        public readonly ?int $nbTotalItems = null,
        public readonly DateTimeImmutable $startedAt = new DateTimeImmutable(),
        public readonly ?DateTimeImmutable $endedAt = null,
        public readonly mixed $output = null,
        private readonly int $nbLoadedItemsSinceLastFlush = 0,
        private bool $flush = false,
        public array $context = [],
    ) {
    }

    /**
     * Flush after current item.
     */
    public function flush(): void
    {
        $this->flush = true;
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

    public function shouldFlush(): bool
    {
        return $this->flush
            || (0 === ($this->nbLoadedItemsSinceLastFlush % $this->options->flushEvery));
    }

    public function getDuration(): float
    {
        $endedAt = $this->endedAt ?? new DateTimeImmutable();

        return (float) ($endedAt->format('U.u') - $this->startedAt->format('U.u'));
    }

    /**
     * @internal
     */
    public function withUpdatedItemKey(mixed $key): self
    {
        return $this->clone([
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
        return $this->clone([
            'nbLoadedItems' => $this->nbLoadedItems + 1,
            'nbLoadedItemsSinceLastFlush' => $this->nbLoadedItemsSinceLastFlush + 1,
        ]);
    }

    /**
     * @internal
     */
    public function withNbTotalItems(?int $nbTotalItems): self
    {
        return $this->clone(['nbTotalItems' => $nbTotalItems]);
    }

    /**
     * @internal
     */
    public function withOutput(mixed $output): self
    {
        return $this->clone(['output' => $output]);
    }

    /**
     * @internal
     */
    public function withClearedFlush(): self
    {
        return $this->clone([
            'flush' => false,
            'nbLoadedItemsSinceLastFlush' => 0,
        ]);
    }
}
