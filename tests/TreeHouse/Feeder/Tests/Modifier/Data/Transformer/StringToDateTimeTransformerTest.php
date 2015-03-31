<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\StringToDateTimeTransformer;

class StringToDateTimeTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return [
            ['Y-m-d H:i:s', '2010-02-03 16:05:06', '2010-02-03 16:05:06 UTC'],
            ['Y-m-d H:i:00', '2010-02-03 16:05:00', '2010-02-03 16:05:00 UTC'],
            ['Y-m-d H:i', '2010-02-03 16:05', '2010-02-03 16:05:00 UTC'],
            ['Y-m-d H', '2010-02-03 16', '2010-02-03 16:00:00 UTC'],
            ['Y-m-d', '2010-02-03', '2010-02-03 00:00:00 UTC'],
            ['Y-m', '2010-12', '2010-12-01 00:00:00 UTC'],
            ['Y', '2010', '2010-01-01 00:00:00 UTC'],
            ['d-m-Y', '03-02-2010', '2010-02-03 00:00:00 UTC'],
            ['H:i:s', '16:05:06', '1970-01-01 16:05:06 UTC'],
            ['H:i:00', '16:05:00', '1970-01-01 16:05:00 UTC'],
            ['H:i', '16:05', '1970-01-01 16:05:00 UTC'],
            ['H', '16', '1970-01-01 16:00:00 UTC'],

            // different day representations
            ['Y-m-j', '2010-02-3', '2010-02-03 00:00:00 UTC'],
            ['z', '33', '1970-02-03 00:00:00 UTC'],

            // different month representations
            ['Y-n-d', '2010-2-03', '2010-02-03 00:00:00 UTC'],
            ['Y-M-d', '2010-Feb-03', '2010-02-03 00:00:00 UTC'],
            ['Y-F-d', '2010-February-03', '2010-02-03 00:00:00 UTC'],

            // different year representations
            ['y-m-d', '10-02-03', '2010-02-03 00:00:00 UTC'],

            // different time representations
            ['G:i:s', '16:05:06', '1970-01-01 16:05:06 UTC'],
            ['g:i:s a', '4:05:06 pm', '1970-01-01 16:05:06 UTC'],
            ['h:i:s a', '04:05:06 pm', '1970-01-01 16:05:06 UTC'],

            // seconds since Unix
            ['U', '1265213106', '2010-02-03 16:05:06 UTC'],

            ['Y-z', '2010-33', '2010-02-03 00:00:00 UTC'],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testTransform($format, $input, $output)
    {
        $transformer = new StringToDateTimeTransformer($format, 'UTC', 'UTC');

        $this->assertEquals(new \DateTime($output), $transformer->transform($input));
    }

    public function testTransformEmpty()
    {
        $transformer = new StringToDateTimeTransformer();

        $this->assertNull($transformer->transform(null));
        $this->assertNull($transformer->transform(''));
    }

    public function testTransformWithDifferentTimezones()
    {
        $transformer = new StringToDateTimeTransformer('Y-m-d H:i:s', 'Asia/Hong_Kong', 'America/New_York');

        $output = new \DateTime('2010-02-03 12:05:06 America/New_York');
        $input = $output->format('Y-m-d H:i:s');
        $output->setTimezone(new \DateTimeZone('Asia/Hong_Kong'));

        $this->assertEquals($output, $transformer->transform($input));
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\UnexpectedTypeException
     */
    public function testUnexpectedTimezoneType()
    {
        new StringToDateTimeTransformer('Y-m-d H:i:s', 1234);
    }

    /**
     * @dataProvider      invalidTimezoneProvider
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidTimezone($inputTz = null, $outputTz = null)
    {
        new StringToDateTimeTransformer('Y-m-d H:i:s', $inputTz, $outputTz);
    }

    public function invalidTimezoneProvider()
    {
        return [
            ['OST'],
            ['UTC', 'OST'],
        ];
    }

    /**
     * @dataProvider      invalidDateProvider
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testTransformExpectsValidDateTime($date)
    {
        (new StringToDateTimeTransformer())->transform($date);
    }

    public function invalidDateProvider()
    {
        return [
            [[]],
            [2012],
            ['2010-04-31'],
            ['2010-2010-2010'],
            [new \DateTime()],
        ];
    }
}
