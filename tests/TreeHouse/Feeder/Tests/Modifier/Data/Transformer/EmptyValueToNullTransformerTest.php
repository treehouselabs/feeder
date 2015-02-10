<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\EmptyValueToNullTransformer;

class EmptyValueToNullTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmptyValueToNullTransformer
     */
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new EmptyValueToNullTransformer();
    }

    /**
     * @dataProvider getEmptyValues
     */
    public function testEmptyValues($value)
    {
        $this->assertNull($this->transformer->transform($value));
    }

    public static function getEmptyValues()
    {
        return array(
            array(null),
            array(''),
            array(array()),
        );
    }

    /**
     * @dataProvider getNonEmptyValues
     */
    public function testNonEmptyValues($value)
    {
        $this->assertNotNull($this->transformer->transform($value));
    }

    public static function getNonEmptyValues()
    {
        return array(
            array(0),
            array('0'),
            array(0.0),
            array(false),
            array(true),
            array('foo'),
            array(1234),
            array(['foo', 'bar'])
        );
    }
}
