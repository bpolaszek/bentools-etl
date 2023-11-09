<?php

declare(strict_types=1);

namespace BenTools\ETL\Transformer;

use BenTools\ETL\EtlState;
use BenTools\ETL\Internal\TransformResult;

final readonly class ChainTransformer implements TransformerInterface
{
    /**
     * @var TransformerInterface[]
     */
    private array $transformers;

    public function __construct(
        TransformerInterface|callable $transformer,
        TransformerInterface|callable ...$transformers
    ) {
        $transformers = [$transformer, ...$transformers];
        foreach ($transformers as $t => $_transformer) {
            if (!$_transformer instanceof TransformerInterface) {
                $transformers[$t] = new CallableTransformer($_transformer(...));
            }
        }
        $this->transformers = $transformers;
    }

    public function with(TransformerInterface|callable $transformer): self
    {
        return new self(...[...$this->transformers, $transformer]);
    }

    public function transform(mixed $item, EtlState $state): mixed
    {
        $output = $item;
        foreach ($this->transformers as $transformer) {
            $output = TransformResult::create($transformer->transform($output, $state))->value;
        }

        return $output;
    }
}
