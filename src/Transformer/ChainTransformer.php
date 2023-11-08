<?php

declare(strict_types=1);

namespace Bentools\ETL\Transformer;

use Bentools\ETL\EtlState;

final readonly class ChainTransformer implements TransformerInterface
{
    /**
     * @var TransformerInterface[]
     */
    private array $transformers;

    public function __construct(TransformerInterface|callable ...$transformers)
    {
        foreach ($transformers as $t => $transformer) {
            if (!$transformer instanceof TransformerInterface) {
                $transformers[$t] = new CallableTransformer($transformer(...));
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
            $output = $transformer->transform($output, $state);
        }

        return $output;
    }
}
