<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\ExpandAttributesTransformer;

class ExpandAttributesTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParameterBag
     */
    protected $item;

    protected function setUp()
    {
        $this->item = new ParameterBag([
            '@id' => 1234,
            'img' => [
                '@src' => 'foo',
                'bar' => 'baz',
            ],
        ]);
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\UnexpectedTypeException
     */
    public function testInvalidConstructor()
    {
        new ExpandAttributesTransformer(1234);
    }

    public function testExpandField()
    {
        $transformer = new ExpandAttributesTransformer('img');
        $transformer->transform($this->item);

        $this->assertEquals(
            [
                '@id' => 1234,
                'img' => [
                    '@src' => 'foo',
                    'src' => 'foo',
                    'bar' => 'baz',
                ],
            ],
            $this->item->all()
        );
    }

    public function testExpandFieldNoArray()
    {
        $item = new ParameterBag([
            '@id' => 1234,
            'img' => 'foo',
        ]);

        $transformer = new ExpandAttributesTransformer('img');
        $transformer->transform($item);

        $this->assertEquals(
            [
                '@id' => 1234,
                'img' => 'foo',
            ],
            $item->all()
        );
    }

    public function testExpandRootAttributes()
    {
        $transformer = new ExpandAttributesTransformer();
        $transformer->transform($this->item);

        $this->assertEquals(
            [
                '@id' => 1234,
                'id' => 1234,
                'img' => [
                    '@src' => 'foo',
                    'bar' => 'baz',
                ],
            ],
            $this->item->all()
        );
    }

    public function testRemoveOriginal()
    {
        $transformer = new ExpandAttributesTransformer('img', true);
        $transformer->transform($this->item);

        $this->assertEquals(
            [
                '@id' => 1234,
                'img' => [
                    'src' => 'foo',
                    'bar' => 'baz',
                ],
            ],
            $this->item->all()
        );
    }

    public function testRemoveOriginalRootAttribute()
    {
        $transformer = new ExpandAttributesTransformer(null, true);
        $transformer->transform($this->item);

        $this->assertEquals(
            [
                'id' => 1234,
                'img' => [
                    '@src' => 'foo',
                    'bar' => 'baz',
                ],
            ],
            $this->item->all()
        );
    }

    public function testDontOverwriteExisting()
    {
        $item = new ParameterBag([
            'img' => [
                '@src' => 'foo',
                'src' => 'baz',
            ],
        ]);

        $transformer = new ExpandAttributesTransformer('img', true);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'img' => [
                    'src' => 'baz',
                ],
            ],
            $item->all()
        );
    }

    public function testOverwriteExisting()
    {
        $item = new ParameterBag([
            'img' => [
                '@src' => 'foo',
                'src' => 'baz',
            ],
        ]);

        $transformer = new ExpandAttributesTransformer('img', true, ['src']);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'img' => [
                    'src' => 'foo',
                ],
            ],
            $item->all()
        );
    }

    public function testDontOverwriteExistingRootAttribute()
    {
        $item = new ParameterBag([
            '@id' => 1234,
            'id' => 'foo',
        ]);

        $transformer = new ExpandAttributesTransformer(null, true);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'id' => 'foo',
            ],
            $item->all()
        );
    }

    public function testOverwriteExistingRootAttribute()
    {
        $item = new ParameterBag([
            '@id' => 1234,
            'id' => 'foo',
        ]);

        $transformer = new ExpandAttributesTransformer(null, true, ['id']);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'id' => 1234,
            ],
            $item->all()
        );
    }
}
