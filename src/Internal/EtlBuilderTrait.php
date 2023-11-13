<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\Extractor\CallableExtractor;
use BenTools\ETL\Extractor\ChainExtractor;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Loader\CallableLoader;
use BenTools\ETL\Loader\ChainLoader;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Recipe\Recipe;
use BenTools\ETL\Transformer\CallableTransformer;
use BenTools\ETL\Transformer\ChainTransformer;
use BenTools\ETL\Transformer\TransformerInterface;

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

    public function extractFrom(
        ExtractorInterface|callable $extractor,
        ExtractorInterface|callable ...$extractors
    ): self {
        $extractors = [$extractor, ...$extractors];

        foreach ($extractors as $e => $_extractor) {
            if (!$_extractor instanceof ExtractorInterface) {
                $extractors[$e] = new CallableExtractor($_extractor(...));
            }
        }

        return $this->cloneWith(['extractor' => new ChainExtractor(...$extractors)]);
    }

    public function transformWith(
        TransformerInterface|callable $transformer,
        TransformerInterface|callable ...$transformers
    ): self {
        $transformers = [$transformer, ...$transformers];

        foreach ($transformers as $t => $_transformer) {
            if (!$_transformer instanceof TransformerInterface) {
                $transformers[$t] = new CallableTransformer($_transformer(...));
            }
        }

        return $this->cloneWith(['transformer' => new ChainTransformer(...$transformers)]);
    }

    public function loadInto(LoaderInterface|callable $loader, LoaderInterface|callable ...$loaders): self
    {
        $loaders = [$loader, ...$loaders];

        foreach ($loaders as $l => $_loader) {
            if (!$_loader instanceof LoaderInterface) {
                $loaders[$l] = new CallableLoader($_loader(...));
            }
        }

        return $this->cloneWith(['loader' => new ChainLoader(...$loaders)]);
    }

    public function withOptions(EtlConfiguration $configuration): self
    {
        return $this->cloneWith(['options' => $configuration]);
    }

    public function withRecipe(Recipe|callable $recipe, Recipe|callable ...$recipes): self
    {
        foreach ([$recipe, ...$recipes] as $_recipe) {
            if (!$_recipe instanceof Recipe) {
                $_recipe = Recipe::fromCallable($_recipe);
            }
            $executor = $_recipe->decorate($this);
        }

        return $executor;
    }
}
