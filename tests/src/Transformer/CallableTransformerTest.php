<?php

namespace BenTools\ETL\Tests\Transformer;

use BenTools\ETL\Transformer\CallableTransformer;
use PHPUnit\Framework\TestCase;
use function BenTools\ETL\Tests\dummy_etl;

class CallableTransformerTest extends TestCase
{

    public function testTransform()
    {
        $item = 'CAPS ARE HELL';
        $transform = new CallableTransformer('strtolower');
        $transformed = $transform->transform($item, 0, dummy_etl());
        $this->assertSame('caps are hell', \iterator_to_array($transformed)[0]);

    }
}
