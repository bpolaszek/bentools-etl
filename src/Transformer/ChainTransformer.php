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

    public function transform(mixed $item, EtlState $state): mixed
    {
        $items = [$item];
        $fanned = false;

        foreach ($this->transformers as $transformer) {
            $nextItems = [];
            foreach ($items as $currentItem) {
                $result = $transformer->transform($currentItem, $state);
                if ($result instanceof Generator) {
                    $fanned = true;
                    array_push($nextItems, ...$result);
                } else {
                    $nextItems[] = $result;
                }
            }
            $items = $nextItems;
        }

        if (!$fanned) {
            return $items[0];
        }

        return (static function (array $items): Generator {
            yield from $items;
        })($items);
    }

    public static function from(TransformerInterface $transformer): self
    {
        return match ($transformer instanceof self) {
            true => $transformer,
            false => new self($transformer),
        };
    }
}
