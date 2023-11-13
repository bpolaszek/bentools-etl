<?php

declare(strict_types=1);

namespace BenTools\ETL\Loader;

use BenTools\ETL\EtlState;
use BenTools\ETL\Internal\ConditionalLoaderTrait;

final readonly class ChainLoader implements LoaderInterface
{
    use ConditionalLoaderTrait;

    /**
     * @var LoaderInterface[]
     */
    private array $loaders;

    public function __construct(
        LoaderInterface|callable $loader,
        LoaderInterface|callable ...$loaders,
    ) {
        $loaders = [$loader, ...$loaders];
        foreach ($loaders as $l => $_loader) {
            if (!$_loader instanceof LoaderInterface) {
                $loaders[$l] = new CallableLoader($_loader(...));
            }
        }
        $this->loaders = $loaders;
    }

    public function with(
        LoaderInterface|callable $loader,
        LoaderInterface|callable ...$loaders,
    ): self {
        return new self(...[...$this->loaders, $loader, ...$loaders]);
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

    public static function from(LoaderInterface $loader): self
    {
        return match ($loader instanceof self) {
            true => $loader,
            false => new self($loader),
        };
    }
}
