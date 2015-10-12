<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\CallbackTransformer;

class CallbackTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $transformer = new CallbackTransformer(function () {});

        $this->assertInstanceOf(CallbackTransformer::class, $transformer);
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\UnexpectedTypeException
     */
    public function testInvalidConstructor()
    {
        new CallbackTransformer(true);
    }

    public function testTransform()
    {
        $filter = new CallbackTransformer(function (ParameterBag $item) {
            $item->set('foo', 'bar');
        });

        $item = new ParameterBag();
        $filter->transform($item);

        $this->assertSame('bar', $item->get('foo'));
    }
}
