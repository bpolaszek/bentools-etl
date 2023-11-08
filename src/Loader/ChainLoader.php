<?php

declare(strict_types=1);

namespace Bentools\ETL\Loader;

use Bentools\ETL\EtlState;
use Bentools\ETL\Internal\ConditionalLoaderTrait;

final readonly class ChainLoader implements LoaderInterface
{
    use ConditionalLoaderTrait;

    /**
     * @var LoaderInterface[]
     */
    private array $loaders;

    public function __construct(
        LoaderInterface|callable ...$loaders
    ) {
        foreach ($loaders as $l => $loader) {
            if (!$loader instanceof LoaderInterface) {
                $loaders[$l] = new CallableLoader($loader(...));
            }
        }
        $this->loaders = $loaders;
    }

    public function with(LoaderInterface|callable $loader): self
    {
        return new self(...[...$this->loaders, $loader]);
    }

    public function load(mixed $item, EtlState $state): void
    {
        foreach ($this->loaders as $loader) {
            if (self::shouldLoad($loader, $item, $state)) {
                $loader->load($item, $state);
            }
        }
    }

    public function flush(bool $isPartial, EtlState $state): mixed
    {
        foreach ($this->loaders as $loader) {
            $output = $loader->flush($isPartial, $state);
        }

        return $output ?? null;
    }
}
