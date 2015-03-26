<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\EnumeratedStringToArrayTransformer;

class EnumeratedStringToArrayTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnumeratedStringToArrayTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $this->transformer = new EnumeratedStringToArrayTransformer([',', '/', '+', 'and']);
    }

    public function testDefaults()
    {
        $transformer = new EnumeratedStringToArrayTransformer();
        $this->assertEquals(['foo', 'bar'], $transformer->transform('foo, bar'));
    }

    /**
     * @dataProvider getTestData
     */
    public function testEnumeratedStrings($string, array $expected)
    {
        $this->assertEquals($expected, $this->transformer->transform($string));
    }

    public static function getTestData()
    {
        return [
            ['foo, bar', ['foo', 'bar']],
            ['foo/bar', ['foo', 'bar']],
            ['foo / bar', ['foo', 'bar']],
            ['foo, bar and baz', ['foo', 'bar', 'baz']],
            ['foo,bar +baz', ['foo', 'bar', 'baz']],
        ];
    }

    public function testDontTransformNull()
    {
        $this->assertNull($this->transformer->transform(null));
    }

    public function testDontTransformArray()
    {
        $this->assertEquals([], $this->transformer->transform([]));
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testInvalidTransformationValue()
    {
        $this->transformer->transform(new \stdClass());
    }
}
