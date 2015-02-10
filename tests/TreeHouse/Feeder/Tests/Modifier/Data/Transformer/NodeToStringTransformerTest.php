<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\NodeToStringTransformer;

class NodeToStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NodeToStringTransformer
     */
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new NodeToStringTransformer();
    }

    /**
     * @dataProvider getTestData
     */
    public function testNodes($test, $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($test));
    }

    public static function getTestData()
    {
        return [
            ['Foo', 'Foo'],
            [['Foo'], ['Foo']],
            [['#' => 'Foo'], 'Foo'],
        ];
    }
}
