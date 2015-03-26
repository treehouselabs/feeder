<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\TrimTransformer;

class TrimTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformer()
    {
        $item = new ParameterBag([
            'string' => ' foo ',
            'integer' => 1234,
            'boolean' => true,
            'array' => [
                'foo' => ' bar ',
            ],
        ]);

        $transformer = new TrimTransformer();
        $transformer->transform($item);

        $this->assertSame(
            [
                'string' => 'foo',
                'integer' => 1234,
                'boolean' => true,
                'array' => [
                    'foo' => 'bar',
                ],

            ],
            $item->all()
        );
    }
}
