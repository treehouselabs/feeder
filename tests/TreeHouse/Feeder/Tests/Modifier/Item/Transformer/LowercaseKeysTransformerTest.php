<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\LowercaseKeysTransformer;

class LowercaseKeysTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testLowercaseKeys()
    {
        $item = new ParameterBag([
            'foo' => 'bar',
            'NaN' => 'not a number',
            'BAR' => [
                'drINks' => 'on me',
            ],
        ]);

        $transformer = new LowercaseKeysTransformer();
        $transformer->transform($item);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'nan' => 'not a number',
                'bar' => [
                    'drinks' => 'on me',
                ],
            ],
            $item->all()
        );
    }
}
