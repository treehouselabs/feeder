<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\TransformationFailedException;
use TreeHouse\Feeder\Modifier\Data\Transformer\CallbackTransformer;
use TreeHouse\Feeder\Modifier\Item\Transformer\DataTransformer;
use TreeHouse\Feeder\Modifier\Item\Transformer\TransformerInterface;

class DataTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformer()
    {
        $innerTransformer = new CallbackTransformer(function ($value) {
            return $value . 'bar';
        });

        $transformer = new DataTransformer($innerTransformer, 'foo');

        $this->assertInstanceOf(TransformerInterface::class, $transformer);

        $this->assertEquals('foo', $transformer->getField());
        $this->assertSame($innerTransformer, $transformer->getInnerTransformer());
    }

    public function testTransform()
    {
        $transformer = new DataTransformer(
            new CallbackTransformer(function ($value) {
                return $value . 'bar';
            }),
            'foo'
        );

        $item = new ParameterBag(['foo' => 'foo']);
        $transformer->transform($item);

        $this->assertEquals('foobar', $item->get('foo'));
    }

    public function testDontTransformWhenFieldDoesNotExist()
    {
        $transformer = new DataTransformer(
            new CallbackTransformer(function ($value) {
                return $value . 'bar';
            }),
            'foo'
        );

        $item = new ParameterBag(['foobar' => 'foo']);
        $transformer->transform($item);

        $this->assertEquals('foo', $item->get('foobar'));
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testTransformationException()
    {
        $transformer = new DataTransformer(
            new CallbackTransformer(function ($value) {
                throw new TransformationFailedException();
            }),
            'foo'
        );

        $item = new ParameterBag(['foo' => 'foo']);
        try {
            $transformer->transform($item);
        } catch (TransformationFailedException $e) {
            $this->assertNull($item->get('foo'));

            throw $e;
        }
    }
}
