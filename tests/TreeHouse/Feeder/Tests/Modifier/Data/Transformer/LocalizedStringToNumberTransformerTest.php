<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\LocalizedStringToNumberTransformer;

/**
 * Test LocalizedStringToNumberTransformer
 */
class LocalizedStringToNumberTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test construction
     */
    public function testConstructor()
    {
        $transformer = new LocalizedStringToNumberTransformer();

        $this->assertInstanceOf(LocalizedStringToNumberTransformer::class, $transformer);
    }

    /**
     * @dataProvider stringNumberProvider
     */
    public function testTransform($string, $type, $precision, $grouping, $roundingMode, $expectedResult, $expectedType)
    {
        $transformer = new LocalizedStringToNumberTransformer($type, $precision, $grouping, $roundingMode);

        $result = $transformer->transform($string);

        $this->assertEquals($expectedType, gettype($result));
        $this->assertEquals($expectedResult, $result);
    }

    public function stringNumberProvider()
    {
        return [
            ["1,34", \NumberFormatter::TYPE_DOUBLE, 2, false, \NumberFormatter::ROUND_HALFDOWN, 1.34, 'double'],
            ["1,34", \NumberFormatter::TYPE_INT32, 2, false,  \NumberFormatter::ROUND_HALFDOWN, 1, 'integer'],
            ["1", \NumberFormatter::TYPE_INT32, 0, false,  \NumberFormatter::ROUND_HALFDOWN, 1, 'integer']
        ];
    }
}

