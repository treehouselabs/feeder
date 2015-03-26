<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\CallbackTransformer;
use TreeHouse\Feeder\Modifier\Data\Transformer\TransformerInterface;

class CallbackTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $transformer = new CallbackTransformer(function () {});

        $this->assertInstanceOf(TransformerInterface::class, $transformer);
    }

    /**
     * @dataProvider      getInvalidConstructorData
     * @expectedException \TreeHouse\Feeder\Exception\UnexpectedTypeException
     */
    public function testInvalidConstructor($arg)
    {
        new CallbackTransformer($arg);
    }

    public function getInvalidConstructorData()
    {
        return [
            [null],
            ['foo'],
            [1234],
            [[]],
        ];
    }

    public function testTransform()
    {
        $transformer = new CallbackTransformer(function ($value) { return 'bar'; });

        $this->assertEquals('bar', $transformer->transform('foo'));
    }
}
