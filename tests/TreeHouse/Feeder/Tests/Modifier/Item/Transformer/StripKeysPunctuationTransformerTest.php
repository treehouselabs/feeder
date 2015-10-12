<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\StripKeysPunctuationTransformer;

class StripKeysPunctuationTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testLowercaseKeys()
    {
        $item = new ParameterBag([
            'f.o.o' => 'foo',
            'b,ar' => 'bar',
            'baz' => [
                'foo:baz' => 'foobaz1',
                'foo;baz' => 'foobaz2',
            ],
        ]);

        $transformer = new StripKeysPunctuationTransformer();
        $transformer->transform($item);

        $this->assertEquals(
            [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => [
                    'foobaz' => 'foobaz2',
                ],
            ],
            $item->all()
        );
    }

    public function testLowercaseCustomKeys()
    {
        $item = new ParameterBag([
            'f.o.o' => 'foo',
            'b&ar' => 'bar',
        ]);

        $transformer = new StripKeysPunctuationTransformer(['&']);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'f.o.o' => 'foo',
                'bar' => 'bar',
            ],
            $item->all()
        );
    }
}
