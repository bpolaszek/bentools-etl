<?php

namespace BenTools\ETL;

use BenTools\ETL\EventDispatcher\EtlEvents;
use BenTools\ETL\EventDispatcher\EventDispatcher;
use BenTools\ETL\EventDispatcher\EventListener;
use BenTools\ETL\Extractor\ExtractorInterface;
use BenTools\ETL\Loader\LoaderInterface;
use BenTools\ETL\Recipe\Recipe;
use BenTools\ETL\Transformer\TransformerInterface;

final class EtlBuilder
{
    /**
     * @var callable|null
     */
    private $extractor;

    /**
     * @var callable|null
     */
    private $transformer;

    /**
     * @var callable|null
     */
    private $initLoader;

    /**
     * @var callable
     */
    private $loader;

    /**
     * @var callable|null
     */
    private $committer;

    /**
     * @var callable|null
     */
    private $restorer;

    /**
     * @var int|null
     */
    private $flushEvery;

    /**
     * @var EventListener[]
     */
    private $listeners;

    /**
     * EtlBuilder constructor.
     */
    private function __construct($extractor = null, $transformer = null, $loader = null)
    {
        if (null !== $extractor) {
            $this->extractFrom($extractor);
        }

        if (null !== $transformer) {
            $this->transformWith($transformer);
        }

        if (null !== $loader) {
            $this->loadInto($loader);
        }
    }

    /**
     * @return EtlBuilder
     */
    public static function init($extractor = null, $transformer = null, $loader = null): self
    {
        return new self($extractor, $transformer, $loader);
    }

    /**
     * @param $extractor
     * @return EtlBuilder
     * @throws \InvalidArgumentException
     */
    public function extractFrom($extractor): self
    {
        if ($extractor instanceof ExtractorInterface) {
            $this->extractor = [$extractor, 'extract'];

            return $this;
        }

        if (\is_callable($extractor) || null === $extractor) {
            $this->extractor = $extractor;

            return $this;
        }


        throw new \InvalidArgumentException(sprintf('Expected callable, null or instance of %s, got %s', ExtractorInterface::class, \is_object($extractor) ? \get_class($extractor) : \gettype($extractor)));
    }

    /**
     * @param $transformer
     * @return EtlBuilder
     * @throws \InvalidArgumentException
     */
    public function transformWith($transformer): self
    {

        if ($transformer instanceof TransformerInterface) {
            $this->transformer = [$transformer, 'transform'];

            return $this;
        }

        if (\is_callable($transformer) || null === $transformer) {
            $this->transformer = $transformer;

            return $this;
        }

        throw new \InvalidArgumentException(sprintf('Expected callable, null or instance of %s, got %s', TransformerInterface::class, \is_object($transformer) ? \get_class($transformer) : \gettype($transformer)));
    }

    /**
     * @param $loader
     * @return EtlBuilder
     * @throws \InvalidArgumentException
     */
    public function loadInto($loader): self
    {
        if ($loader instanceof LoaderInterface) {
            $this->loader = [$loader, 'load'];
            $this->initLoader = [$loader, 'init'];
            $this->committer = [$loader, 'commit'];
            $this->restorer = [$loader, 'rollback'];

            return $this;
        }

        if (\is_callable($loader)) {
            $this->loader = $loader;

            return $this;
        }


        throw new \InvalidArgumentException(sprintf('Expected callable or instance of %s, got %s', LoaderInterface::class, \is_object($loader) ? \get_class($loader) : \gettype($loader)));
    }

    /**
     * @param int|null $nbItems
     * @return EtlBuilder
     */
    public function flushEvery(?int $nbItems): self
    {
        $this->flushEvery = $nbItems;

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onStart(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::START, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onExtract(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::EXTRACT, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onExtractException(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::EXTRACT_EXCEPTION, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onTransform(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::TRANSFORM, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onTransformException(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::TRANSFORM_EXCEPTION, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onLoad(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::LOAD, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onLoadException(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::LOAD_EXCEPTION, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onFlush(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::FLUSH, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onSkip(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::SKIP, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onStop(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::STOP, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onRollback(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::ROLLBACK, $callable, $priority);

        return $this;
    }

    /**
     * @param callable $callable
     * @param int      $priority
     * @return EtlBuilder
     */
    public function onEnd(callable $callable, int $priority = 0): self
    {
        $this->listeners[] = new EventListener(EtlEvents::END, $callable, $priority);

        return $this;
    }

    /**
     * @param Recipe $recipe
     * @return EtlBuilder
     */
    public function useRecipe(Recipe $recipe): self
    {
        return $recipe->updateBuilder($this);
    }

    /**
     * @return Etl
     * @throws \RuntimeException
     */
    public function createEtl(): Etl
    {
        $this->checkValidity();

        return new Etl(
            $this->extractor,
            $this->transformer,
            $this->loader,
            $this->initLoader,
            $this->committer,
            $this->restorer,
            $this->flushEvery,
            new EventDispatcher($this->listeners)
        );
    }

    /**
     * @return bool
     */
    private function checkValidity(): void
    {
        if (null === $this->loader) {
            throw new \RuntimeException('Loader has not been provided.');
        }

        if (null !== $this->flushEvery && $this->flushEvery <= 0) {
            throw new \RuntimeException('The "flushEvery" option must be null or an integer > 0.');
        }
    }
}
