<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\RemoveFieldsTransformer;

class RemoveFieldsTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testRemoveFields()
    {
        $item = new ParameterBag([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $transformer = new RemoveFieldsTransformer(['foo']);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'bar' => 'baz',
            ],
            $item->all()
        );
    }
}
