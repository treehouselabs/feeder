<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Transformer\UnderscoreKeysTransformer;

class UnderscoreKeysTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testUnderscoreKeys()
    {
        $item = new ParameterBag([
            'testScore'   => 'test',
            'test1Score'  => 'test',
            '2TestScore'  => 'test',
            '3test_Score' => 'test',
            '4test-Score' => 'test',
            'TesTScore'   => 'test',
            'testscore'   => [
                'underScore' => 'test',
            ],
        ]);

        $transformer = new UnderscoreKeysTransformer();
        $transformer->transform($item);

        $this->assertEquals(
            [
                'test_score'  => 'test',
                'test1score'  => 'test',
                '2test_score' => 'test',
                '3test_score' => 'test',
                '4test_score' => 'test',
                'tes_tscore' => 'test',
                'testscore'   => [
                    'under_score' => 'test',
                ],
            ],
            $item->all()
        );
    }
}
