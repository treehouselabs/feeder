<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\EmptyValueToNullTransformer;
use TreeHouse\Feeder\Modifier\Item\Transformer\RecursiveTransformer;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Test recursive an array, EmptyValueToNullTransformer is used to test.
 */
class RecursiveTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestvalues
     */
    public function testTransformer(array $testdata, array $expected)
    {
        $transformer = new RecursiveTransformer(new EmptyValueToNullTransformer());

        $item = new ParameterBag($testdata);
        $transformer->transform($item);
        $result = $item->all();

        $this->assertSame($result, $expected);
    }

    public static function getTestvalues()
    {
        return [
            [
                ['array', ['value1', 'value2', '', ['value3', 'value4', '']]],
                ['array', ['value1', 'value2', null, ['value3', 'value4', null]]]
            ],
            [
                ['key' => 'value'],
                ['key' => 'value']
            ],
            [
                ['key' => 'value', 'aap' => 'banaan'],
                ['key' => 'value', 'aap' => 'banaan']
            ],
        ];
    }
}
