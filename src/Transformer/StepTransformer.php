<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Context\ContextElementInterface;

class StepTransformer implements TransformerInterface
{
    protected $steps = [];
    protected $transformers = [];
    protected $stoppedSteps = [];
    protected $stop = false;

    /**
     * StepTransformer constructor.
     * @param array $steps
     */
    public function __construct(array $steps = [])
    {
        $this->registerSteps($steps);
    }

    /**
     * Register steps, in the order they should be executed.
     *
     * @param array $steps
     */
    public function registerSteps(array $steps): void
    {
        foreach ($steps as $step) {
            if (!is_scalar($step)) {
                throw new \InvalidArgumentException("Steps must be an array of scalar values.");
            }
        }

        $this->steps = $steps;
    }

    /**
     * Register one or several transformers for a particular step.
     *
     * @param                               $step
     * @param callable|TransformerInterface $transformer
     * @param int                           $priority
     * @throws \InvalidArgumentException
     */
    public function registerTransformer($step, callable $transformer, int $priority = 0): void
    {
        if (!in_array($step, $this->steps)) {
            throw new \InvalidArgumentException(sprintf('Step "%s" is not registered.', $step));
        }

        $stack = $this->transformers[$step] ?? new TransformerStack();
        $stack->registerTransformer($transformer, $priority);
        $this->transformers[$step] = $stack;
    }

    /**
     * @param null $step
     * @throws \InvalidArgumentException
     */
    public function stop($step = null)
    {
        if (null !== $step) {
            if (!in_array($step, $this->steps)) {
                throw new \InvalidArgumentException(sprintf('Step "%s" is not registered.', $step));
            }
            if (null !== ($transformer = $this->getTransformerFor($step))) {
                $transformer->stop();
                $this->stoppedSteps[] = $step;
            }
        } else {
            $this->stop = true;
        }
    }

    /**
     * @param $step
     * @return TransformerStack|null
     */
    public function getTransformerFor($step)
    {
        return $this->transformers[$step] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(ContextElementInterface $element): void
    {
        foreach ($this->steps as $step) {
            if (false === $this->stop
                && false === in_array($step, $this->stoppedSteps, true)
                && null !== ($transform = $this->getTransformerFor($step))) {
                    $transform($element);
            }
        }
    }
}
