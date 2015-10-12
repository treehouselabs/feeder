<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\JsonToNativeTransformer;

class JsonToNativeTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonToNativeTransformer
     */
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new JsonToNativeTransformer();
    }

    /**
     * @dataProvider getTestData
     */
    public function testData($test, $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($test));
    }

    public static function getTestData()
    {
        return [
            ['true', true],
            ['0', 0],
            ['1234.56', 1234.56],
            ['foo', null],
            ['"foo"', 'foo'],
            ['["foo", "bar"]', ['foo', 'bar']],
            ['{"foo": "bar"}', ['foo' => 'bar']],
        ];
    }
}
