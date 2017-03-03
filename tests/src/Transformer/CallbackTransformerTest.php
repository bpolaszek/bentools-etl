<?php

namespace BenTools\ETL\Tests\Transformer;

use BenTools\ETL\Context\ContextElement;
use BenTools\ETL\Transformer\CallbackTransformer;
use PHPUnit\Framework\TestCase;

class CallbackTransformerTest extends TestCase
{

    public function testTransformerByPHPFunction()
    {
        $element   = new ContextElement('123e4567-e89b-12d3-a456-426655440000', 'CAPS ARE HELL');
        $transform = new CallbackTransformer('strtolower');

        $transform($element);

        $this->assertSame('123e4567-e89b-12d3-a456-426655440000', $element->getId());
        $this->assertSame('caps are hell', $element->getData());
    }

    public function testTransformerByClosure()
    {
        $element   = new ContextElement('123e4567-e89b-12d3-a456-426655440000', ['WTF' => 'CAPS ARE HELL']);
        $transform = new CallbackTransformer(function ($arrayOfStrings) {
            return array_map(function ($string) {
                return strtolower($string);
            }, $arrayOfStrings);
        });

        $transform($element);

        $this->assertSame('123e4567-e89b-12d3-a456-426655440000', $element->getId());
        $this->assertSame(['WTF' => 'caps are hell'], $element->getData());
    }
}
