<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\NormalizedArrayTransformer;

class NormalizedArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testArrays($test, $expected)
    {
        $transformer = new NormalizedArrayTransformer();
        $this->assertEquals($expected, $transformer->transform($test));
    }

    public static function getTestData()
    {
        return [
            [null, null],
            ['Foo', ['Foo']],
        ];
    }

    /**
     * @dataProvider getNestedTestData
     */
    public function testNestedArrays($test, $expected)
    {
        $transformer = new NormalizedArrayTransformer(true);
        $this->assertEquals($expected, $transformer->transform($test));
    }

    public static function getNestedTestData()
    {
        return [
            [null, null],
            ['Foo', ['Foo']],
            [['Foo'], ['Foo']],
            [['Foo' => 'Bar'], [['Foo' => 'Bar']]],
        ];
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testException()
    {
        $transformer = new NormalizedArrayTransformer();
        $transformer->transform(new \stdClass());
    }
}
