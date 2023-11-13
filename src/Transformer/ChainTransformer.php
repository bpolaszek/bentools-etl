<?php

declare(strict_types=1);

namespace BenTools\ETL\Transformer;

use BenTools\ETL\EtlState;
use Generator;

final readonly class ChainTransformer implements TransformerInterface
{
    /**
     * @var TransformerInterface[]
     */
    private array $transformers;

    public function __construct(
        TransformerInterface|callable $transformer,
        TransformerInterface|callable ...$transformers,
    ) {
        $transformers = [$transformer, ...$transformers];
        foreach ($transformers as $t => $_transformer) {
            if (!$_transformer instanceof TransformerInterface) {
                $transformers[$t] = new CallableTransformer($_transformer(...));
            }
        }
        $this->transformers = $transformers;
    }

    public function with(
        TransformerInterface|callable $transformer,
        TransformerInterface|callable ...$transformers,
    ): self {
        return new self(...[...$this->transformers, $transformer, ...$transformers]);
    }

    public function transform(mixed $item, EtlState $state): Generator
    {
        $item = $this->doTransform($item, $state);

        if ($item instanceof Generator) {
            yield from $item;
        } else {
            yield $item;
        }
    }

    private function doTransform(mixed $item, EtlState $state): mixed
    {
        foreach ($this->transformers as $transformer) {
            $item = $transformer->transform($item, $state);
        }

        return $item;
    }

    public static function from(TransformerInterface $transformer): self
    {
        return match ($transformer instanceof self) {
            true => $transformer,
            false => new self($transformer),
        };
    }
}
