<?php

namespace BenTools\ETL\Transformer;

use BenTools\ETL\Context\ContextElementInterface;

class StepTransformer implements TransformerInterface
{
    protected $steps = [];
    protected $transformers = [];

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
     * @param       $step
     * @param array ...$transformer
     * @throws \InvalidArgumentException
     */
    public function registerTransformer($step, callable ...$transformer): void
    {
        if (!in_array($step, $this->steps)) {
            throw new \InvalidArgumentException("Step {$step} is not registered.");
        }

        if (0 === count($transformer)) {
            throw new \InvalidArgumentException(sprintf("At least 1 transformer should be registered for step %s", $step));
        }

        if (1 !== count($transformer)) {
            foreach ($transformer as $callable) {
                $this->registerTransformer($step, $callable);
            }
            return;
        }

        $transformer = $transformer[0];

        if (isset($this->transformers[$step])) { // Step already has transformers
            if ($this->transformers[$step] instanceof TransformerStack) { // There are already multiple transformers for this step
                $this->transformers[$step]->registerTransformer($transformer);
            } else { // Otherwise, we should create a stack of transformers
                $this->transformers[$step] = new TransformerStack([$this->transformers[$step]]);
                $this->transformers[$step]->registerTransformer($transformer);
            }
        } else {
            $this->transformers[$step] = $transformer;
        }
    }

    /**
     * @param $step
     * @return callable|TransformerInterface|null
     */
    private function getTransformerFor($step)
    {
        return $this->transformers[$step] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(ContextElementInterface $element): void
    {
        foreach ($this->steps as $step) {
            if (null !== ($transform = $this->getTransformerFor($step))) {
                if (!$element->shouldSkip() && !$element->shouldStop()) {
                    $transform($element);
                }
            }
        }
    }
}
