<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\ObsoleteFieldsTransformer;

class ObsoleteFieldsTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testRemoveObsoleteFields()
    {
        $item = new ParameterBag([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $transformer = new ObsoleteFieldsTransformer(['foo']);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'foo' => 'bar',
            ],
            $item->all()
        );
    }
}
