<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\ExpandNodeTransformer;

class ExpandNodeTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParameterBag
     */
    protected $item;

    protected function setUp()
    {
        $this->item = new ParameterBag([
            'img' => [
                'src' => 'foo',
                'bar' => 'baz',
            ],
        ]);
    }

    public function testExpandNode()
    {
        $transformer = new ExpandNodeTransformer('img');
        $transformer->transform($this->item);

        $this->assertEquals(
            [
                'src' => 'foo',
                'bar' => 'baz',
                'img' => [
                    'src' => 'foo',
                    'bar' => 'baz',
                ],
            ],
            $this->item->all()
        );
    }

    public function testRemoveOriginal()
    {
        $transformer = new ExpandNodeTransformer('img', true);
        $transformer->transform($this->item);

        $this->assertEquals(
            [
                'src' => 'foo',
                'bar' => 'baz',
            ],
            $this->item->all()
        );
    }

    public function testDontOverwriteExisting()
    {
        $item = new ParameterBag([
            'src' => 'http://example.org',
            'img' => [
                'src' => 'foo',
                'bar' => 'baz',
            ],
        ]);

        $transformer = new ExpandNodeTransformer('img', true);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'src' => 'http://example.org',
                'bar' => 'baz',
            ],
            $item->all()
        );
    }

    public function testOverwriteExisting()
    {
        $item = new ParameterBag([
            'src' => 'http://example.org',
            'img' => [
                'src' => 'foo',
                'bar' => 'baz',
            ],
        ]);

        $transformer = new ExpandNodeTransformer('img', true, ['src']);
        $transformer->transform($item);

        $this->assertEquals(
            [
                'src' => 'foo',
                'bar' => 'baz',
            ],
            $item->all()
        );
    }
}
