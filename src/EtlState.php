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
        public array $context = [],
        public readonly mixed $currentItemKey = null,
        public readonly int $currentItemIndex = -1,
        public readonly int $nbExtractedItems = 0,
        public readonly int $nbLoadedItems = 0,
        public readonly ?int $nbTotalItems = null,
        public readonly DateTimeImmutable $startedAt = new DateTimeImmutable(),
        public readonly ?DateTimeImmutable $endedAt = null,
        public readonly mixed $output = null,
        private readonly int $nbLoadedItemsSinceLastFlush = 0,
        private bool $earlyFlush = false,
    ) {
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

    public function shouldFlush(): bool
    {
        if (INF === $this->options->flushFrequency) {
            return false;
        }

        return $this->earlyFlush
                || (0 === ($this->nbLoadedItemsSinceLastFlush % $this->options->flushFrequency));
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
