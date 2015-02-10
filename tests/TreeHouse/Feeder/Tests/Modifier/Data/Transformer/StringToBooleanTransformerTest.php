<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\StringToBooleanTransformer;

class StringToBooleanTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringToBooleanTransformer
     */
    protected $transformer;

    public function setUp()
    {
        $this->transformer = new StringToBooleanTransformer();
    }

    /**
     * @dataProvider getTestData
     */
    public function testBooleanData($value, $expected)
    {
        $this->assertSame($expected, $this->transformer->transform($value));
    }

    public static function getTestData()
    {
        return [
            [null,  null],  // leave null alone
            ['',    null],  // empty is considered null
            [true,  true],  // leave existing boolean alone
            [false, false], // leave existing boolean alone
            [0,     false], // cast to boolean
            [1,     true], // cast to boolean
            ['0',   false], // cast to boolean
            ['1',   true],  // cast to boolean
            ['foo', true],  // cast to boolean
        ];
    }

    /**
     * @dataProvider getTruthyTestData
     */
    public function testTruthyValues(array $truthyValues, $value)
    {
        $transformer = new StringToBooleanTransformer($truthyValues);
        $this->assertTrue($transformer->transform($value));
    }

    public static function getTruthyTestData()
    {
        return [
            [[], 'yes'],  // yes is a default truthy value
            [[], 'on'],   // as is 'on'
            [[], 'TRUE'], // case insensitive
            [['y'], 'Y'], // custom value
        ];
    }

    /**
     * @dataProvider getFalsyTestData
     */
    public function testFalsyValues(array $falsyValues, $value)
    {
        $transformer = new StringToBooleanTransformer([], $falsyValues);
        $this->assertFalse($transformer->transform($value));
    }

    public static function getFalsyTestData()
    {
        return [
            [[], 'no'],         // no is a default truthy value
            [[], 'off'],        // as is 'off'
            [[], 'False'],      // case insensitive
            [['None'], 'none'], // custom value
        ];
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testUnexpectedValue()
    {
        $this->transformer->transform(new \stdClass());
    }
}
