<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\DateTimeToIso8601Transformer;
use TreeHouse\Feeder\Modifier\Data\Transformer\TransformerInterface;

class DateTimeToIso8601TransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $transformer = new DateTimeToIso8601Transformer();

        $this->assertInstanceOf(TransformerInterface::class, $transformer);
    }

    public function testTransform()
    {
        $transformer = new DateTimeToIso8601Transformer();

        $date = '2010-02-03T04:05:06+0000';
        $this->assertSame($date, $transformer->transform(new \DateTime($date)));
    }

    public function testTransformNull()
    {
        $this->assertNull((new DateTimeToIso8601Transformer())->transform(null));
    }

    /**
     * @dataProvider      invalidDatesProvider
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testTransformRequiresValidDateTime($date)
    {
        $transformer = new DateTimeToIso8601Transformer();
        $transformer->transform($date);
    }

    public function invalidDatesProvider()
    {
        return [
            ['2010-01-01'],
            ['2010-02-03T17:05:06+08:00'],
            ['2010-02-03 04:05:06 America/New_York'],
        ];
    }
}
