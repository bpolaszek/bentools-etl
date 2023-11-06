<?php

declare(strict_types=1);

namespace Bentools\ETL\Internal;

use Bentools\ETL\EtlConfiguration;
use Bentools\ETL\Extractor\CallableExtractor;
use Bentools\ETL\Extractor\ExtractorInterface;
use Bentools\ETL\Loader\CallableLoader;
use Bentools\ETL\Loader\LoaderInterface;
use Bentools\ETL\Recipe\Recipe;
use Bentools\ETL\Transformer\CallableTransformer;
use Bentools\ETL\Transformer\TransformerInterface;

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

    public function transformWith(TransformerInterface|callable $transformer): self
    {
        if (!$transformer instanceof TransformerInterface) {
            $transformer = new CallableTransformer($transformer(...));
        }

        return $this->cloneWith(['transformer' => $transformer]);
    }

    public function loadInto(LoaderInterface|callable $loader): self
    {
        if (!$loader instanceof LoaderInterface) {
            $loader = new CallableLoader($loader(...));
        }

        return $this->cloneWith(['loader' => $loader]);
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

        return $recipe->fork($this);
    }
}
