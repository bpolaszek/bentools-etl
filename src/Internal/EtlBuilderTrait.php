<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\Extractor\CallableExtractor;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Loader\CallableLoader;
use BenTools\ETL\Loader\ChainLoader;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Recipe\Recipe;
use BenTools\ETL\Transformer\CallableTransformer;
use BenTools\ETL\Transformer\ChainTransformer;
use BenTools\ETL\Transformer\TransformerInterface;

use function count;

/**
 * @internal
 *
 * @template T
 */
trait EtlBuilderTrait
{
    /**
     * @use EtlEventListenersTrait<T>
     */
    use EtlEventListenersTrait;

    public function extractFrom(ExtractorInterface|callable $extractor): self
    {
        if (!$extractor instanceof ExtractorInterface) {
            $extractor = new CallableExtractor($extractor(...));
        }

        return $this->cloneWith(['extractor' => $extractor]);
    }

    public function transformWith(TransformerInterface|callable $transformer, TransformerInterface|callable ...$transformers): self
    {
        $transformers = [$transformer, ...$transformers];

        foreach ($transformers as $t => $_transformer) {
            if (!$_transformer instanceof TransformerInterface) {
                $transformers[$t] = new CallableTransformer($_transformer(...));
            }
        }

        if (count($transformers) > 1) {
            return $this->cloneWith(['transformer' => new ChainTransformer(...$transformers)]);
        }

        return $this->cloneWith(['transformer' => $transformers[0]]);
    }

    public function loadInto(LoaderInterface|callable $loader, LoaderInterface|callable ...$loaders): self
    {
        $loaders = [$loader, ...$loaders];

        foreach ($loaders as $l => $_loader) {
            if (!$_loader instanceof LoaderInterface) {
                $loaders[$l] = new CallableLoader($_loader(...));
            }
        }

        if (count($loaders) > 1) {
            return $this->cloneWith(['loader' => new ChainLoader(...$loaders)]);
        }

        return $this->cloneWith(['loader' => $loaders[0]]);
    }

    public function withOptions(EtlConfiguration $configuration): self
    {
        return $this->cloneWith(['options' => $configuration]);
    }

    public function withRecipe(Recipe|callable $recipe): self
    {
        if (!$recipe instanceof Recipe) {
            $recipe = Recipe::fromCallable($recipe);
        }

        return $recipe->decorate($this);
    }
}
