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
        $this->stack = new StepTransformer(['first', 'second']);
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
        $stack->registerTransformer('first', $bar, $foo);
        $stack($context);
        $this->assertEquals('foo', $context->getData());
    }

    public function testAddTransformersWhenOneConfigured()
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
        $stack->registerTransformer('first', $bar, $foo);
        $stack($context);
        $this->assertEquals('foo', $context->getData());
    }

    public function testAddTransformersWhenMultipleConfigured()
    {

        $context = new ContextElement();
        $stack = $this->stack;
        $foo = function (ContextElementInterface $element) {
            $element->setData('foo');
        };
        $bar = function (ContextElementInterface $element) {
            $element->setData('bar');
        };
        $stack->registerTransformer('first', $bar, $foo);
        $stack->registerTransformer('first', $foo, $bar);
        $stack($context);
        $this->assertEquals('bar', $context->getData());
    }

}
