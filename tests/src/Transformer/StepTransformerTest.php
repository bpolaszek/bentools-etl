<?php

namespace BenTools\ETL\Tests\Transformer;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Transformer\StepTransformer;
use PHPUnit\Framework\TestCase;

class StepTransformerTest extends TestCase
{
    /**
     * @var StepTransformer
     */
    private $stack;

    public function setUp()
    {
        $this->stack = new StepTransformer(['first', 'second', 'third']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidSteps()
    {
        $this->stack->registerSteps([new \stdClass()]);
    }

    public function testTransformer()
    {
        $context = new ContextElement();
        $stack = $this->stack;
        $foo = function (ContextElementInterface $element) {
            $element->setData('foo');
        };
        $bar = function (ContextElementInterface $element) {
            $element->setData('bar');
        };
        $stack->registerTransformer('first', $foo);
        $stack->registerTransformer('second', $bar);

        $stack($context);

        $this->assertEquals('bar', $context->getData());

    }

    public function testTransformerWithInvertedSteps()
    {
        $context = new ContextElement();
        $stack = $this->stack;
        $foo = function (ContextElementInterface $element) {
            $element->setData('foo');
        };
        $bar = function (ContextElementInterface $element) {
            $element->setData('bar');
        };
        $stack->registerTransformer('first', $bar);
        $stack->registerTransformer('second', $foo);

        $stack($context);

        $this->assertEquals('foo', $context->getData());
    }

    public function testMultipleTransformers()
    {

        $context = new ContextElement();
        $stack = $this->stack;
        $foo = function (ContextElementInterface $element) {
            $element->setData('foo');
        };
        $bar = function (ContextElementInterface $element) {
            $element->setData('bar');
        };
        $stack->registerTransformer('first', $bar);
        $stack->registerTransformer('first', $foo);
        $stack($context);
        $this->assertEquals('foo', $context->getData());
    }

    public function testMultipleTransformersWithPriority()
    {

        $context = new ContextElement();
        $stack = $this->stack;
        $foo = function (ContextElementInterface $element) {
            $element->setData('foo');
        };
        $bar = function (ContextElementInterface $element) {
            $element->setData('bar');
        };
        $stack->registerTransformer('first', $bar);
        $stack->registerTransformer('first', $foo, 100);
        $stack($context);
        $this->assertEquals('bar', $context->getData());
    }

    public function testStop()
    {
        $context = new ContextElement();
        $stack = $this->stack;
        $foo = function (ContextElementInterface $element) use ($stack) {
            $element->setData('foo');
            $stack->stop();
        };
        $bar = function (ContextElementInterface $element) use ($stack) {
            $element->setData('bar');
            $stack->stop();
        };
        $baz = function (ContextElementInterface $element) use ($stack) {
            $element->setData('baz');
            $stack->stop();
        };
        $stack->registerTransformer('first', $foo);
        $stack->registerTransformer('second', $bar);
        $stack->registerTransformer('third', $baz);
        $stack($context);
        $this->assertEquals('foo', $context->getData());

    }

    public function testStopStep()
    {
        $context = new ContextElement();
        $stack = $this->stack;
        $barHasNotBeenCalled = true;
        $foo = function (ContextElementInterface $element) use ($stack) {
            $element->setData('foo');
            $stack->stop('first');
        };
        $bar = function (ContextElementInterface $element) use ($stack, &$barHasNotBeenCalled) {
            $barHasNotBeenCalled = false;
            $element->setData('bar');
        };
        $baz = function (ContextElementInterface $element) use ($stack) {
            $element->setData('baz');
        };
        $stack->registerTransformer('first', $foo);
        $stack->registerTransformer('first', $bar);
        $stack->registerTransformer('second', $baz);
        $stack($context);
        $this->assertEquals('baz', $context->getData());
        $this->assertTrue($barHasNotBeenCalled);
    }

    public function testStopStepBeforeItBegins()
    {
        $context = new ContextElement();
        $stack = $this->stack;
        $foo = function (ContextElementInterface $element) use ($stack) {
            $element->setData('foo');
            $stack->stop('first');
        };
        $bar = function (ContextElementInterface $element) use ($stack) {
            $element->setData('bar');
            $stack->stop();
        };
        $stack->registerTransformer('first', $foo);
        $stack->registerTransformer('second', $bar);
        $stack->stop('second');
        $stack($context);
        $this->assertEquals('foo', $context->getData());
    }

}
