<?php

namespace BenTools\ETL\Tests\Transformer;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Context\ContextElementInterface;
use BenTools\ETL\Transformer\TransformerStack;
use PHPUnit\Framework\TestCase;

class TransformerStackTest extends TestCase
{

    public function testConstruct()
    {
        $stack = new TransformerStack([
            function () {},
            function () {},
        ]);
        $this->assertCount(2, iterator_to_array($stack));
    }

    public function testAddTransformer()
    {
        $stack = new TransformerStack();
        $this->assertCount(0, iterator_to_array($stack));

        $stack->registerTransformer(function () {});
        $this->assertCount(1, iterator_to_array($stack));

        $stack->registerTransformer(function () {});
        $this->assertCount(2, iterator_to_array($stack));
    }

    public function testDataIsTransformed()
    {
        $stack = new TransformerStack();
        $foo = function (ContextElementInterface $element) {
            $element->setData('foo');
        };
        $bar = function (ContextElementInterface $element) {
            $element->setData('bar');
        };
        $stack->registerTransformer($foo);
        $stack->registerTransformer($bar);

        $context = new ContextElement();
        $stack($context);
        $this->assertEquals('bar', $context->getData());
    }

    public function testTransformerPriorities()
    {
        $stack = new TransformerStack();
        $foo = function (ContextElementInterface $element) {
            $element->setData('foo');
        };
        $bar = function (ContextElementInterface $element) {
            $element->setData('bar');
        };
        $baz = function (ContextElementInterface $element) {
            $element->setData('baz');
        };
        $stack->registerTransformer($foo, 50);
        $stack->registerTransformer($bar, 0);
        $stack->registerTransformer($baz, 100);

        $context = new ContextElement();
        $stack($context);
        $this->assertEquals('bar', $context->getData());
    }

    public function testStop()
    {
        $stack = new TransformerStack();
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
        $stack->registerTransformer($foo, 50);
        $stack->registerTransformer($bar, 100);
        $stack->registerTransformer($baz, 0);

        $context = new ContextElement();
        $stack($context);
        $this->assertEquals('bar', $context->getData());
    }
}
