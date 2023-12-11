<?php

declare(strict_types=1);

namespace BenTools\ETL\Internal;

use BenTools\ETL\EtlConfiguration;
use BenTools\ETL\EtlExecutor;
use BenTools\ETL\Extractor\CallableExtractor;
use BenTools\ETL\Extractor\ChainExtractor;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Loader\CallableLoader;
use BenTools\ETL\Loader\ChainLoader;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Processor\ProcessorInterface;
use BenTools\ETL\Recipe\Recipe;
use BenTools\ETL\Transformer\CallableTransformer;
use BenTools\ETL\Transformer\ChainTransformer;
use BenTools\ETL\Transformer\TransformerInterface;

use function array_intersect_key;
use function count;

/**
 * @internal
 *
 * @template EtlExecutor
 */
trait EtlBuilderTrait
{
    /**
     * @use EtlEventListenersTrait<EtlExecutor>
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

        if (count($extractors) > 1) {
            return $this->cloneWith(['extractor' => new ChainExtractor(...$extractors)]);
        }

        return $this->cloneWith(['extractor' => $extractors[0]]);
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

    public function withRecipe(Recipe|callable $recipe, Recipe|callable ...$recipes): self
    {
        $executor = $this;
        foreach ([$recipe, ...$recipes] as $_recipe) {
            if (!$_recipe instanceof Recipe) {
                $_recipe = Recipe::fromCallable($_recipe);
            }
            $executor = $_recipe->decorate($executor);
        }

        return $executor;
    }

    public function withProcessor(ProcessorInterface $processor): self
    {
        return $this->cloneWith(['processor' => $processor]);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function withContext(array $context, bool $clear = false, bool $overwrite = true): self
    {
        return $this->cloneWith(['context' => [
            ...($clear ? [] : $this->context),
            ...$context,
            ...($overwrite ? [] : array_intersect_key($this->context, $context)),
        ]]);
    }
}
