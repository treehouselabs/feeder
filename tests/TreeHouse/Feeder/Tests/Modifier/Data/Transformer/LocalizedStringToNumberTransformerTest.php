<?php

namespace TreeHouse\Feeder\Tests\Modifier\Data\Transformer;

use TreeHouse\Feeder\Modifier\Data\Transformer\LocalizedStringToNumberTransformer;
use TreeHouse\Feeder\Modifier\Data\Transformer\TransformerInterface;

/**
 * Test LocalizedStringToNumberTransformer
 */
class LocalizedStringToNumberTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        \Locale::setDefault('nl_NL');
    }

    /**
     * Test construction
     */
    public function testConstructor()
    {
        $transformer = new LocalizedStringToNumberTransformer();

        $this->assertInstanceOf(TransformerInterface::class, $transformer);
    }

    /**
     * @dataProvider provideTransformations
     */
    public function testTransform($from, $to, $locale)
    {
        $transformer = new LocalizedStringToNumberTransformer($locale);

        $this->assertEquals($to, $transformer->transform($from));
    }

    public function provideTransformations()
    {
        return [
            ['',          null,      'nl_NL'],
            ['1',         1,         'nl_NL'],
            ['1,5',       1.5,       'nl_NL'],
            ['1234,5',    1234.5,    'nl_NL'],
            ['12345,912', 12345.912, 'nl_NL'],
            ['1234,5',    1234.5,    'ru'],
            ['1234,5',    1234.5,    'fi'],
        ];
    }

    /**
     * @dataProvider provideTransformationsWithGrouping
     */
    public function testTransformWithGrouping($from, $to, $locale)
    {
        $transformer = new LocalizedStringToNumberTransformer($locale, null, true);

        $this->assertEquals($to, $transformer->transform($from));
    }

    public function provideTransformationsWithGrouping()
    {
        return [
            ['1.234,5',    1234.5,    'nl_NL'],
            ['12.345,912', 12345.912, 'nl_NL'],
            ['1 234,5',    1234.5,    'fr'],
            ['1 234,5',    1234.5,    'ru'],
            ['1 234,5',    1234.5,    'fi'],
        ];
    }

    public function testTransformWithGroupingButWithoutGroupSeparator()
    {
        $transformer = new LocalizedStringToNumberTransformer(null, null, true);

        // omit group separator
        $this->assertEquals(1234.5, $transformer->transform('1234,5'));
        $this->assertEquals(12345.912, $transformer->transform('12345,912'));
    }

    /**
     * @dataProvider transformWithRoundingProvider
     */
    public function testTransformWithRounding($precision, $input, $output, $roundingMode)
    {
        $transformer = new LocalizedStringToNumberTransformer(null, $precision, null, $roundingMode);

        $this->assertEquals($output, $transformer->transform($input));
    }

    public function transformWithRoundingProvider()
    {
        return [
            // towards positive infinity (1.6 -> 2, -1.6 -> -1)
            [0,  '1234,5',  1235,  \NumberFormatter::ROUND_CEILING],
            [0,  '1234,4',  1235,  \NumberFormatter::ROUND_CEILING],
            [0, '-1234,5', -1234,  \NumberFormatter::ROUND_CEILING],
            [0, '-1234,4', -1234,  \NumberFormatter::ROUND_CEILING],
            [1,  '123,45',  123.5, \NumberFormatter::ROUND_CEILING],
            [1,  '123,44',  123.5, \NumberFormatter::ROUND_CEILING],
            [1, '-123,45', -123.4, \NumberFormatter::ROUND_CEILING],
            [1, '-123,44', -123.4, \NumberFormatter::ROUND_CEILING],

            // towards negative infinity (1.6 -> 1, -1.6 -> -2)
            [0,  '1234,5',  1234,  \NumberFormatter::ROUND_FLOOR],
            [0,  '1234,4',  1234,  \NumberFormatter::ROUND_FLOOR],
            [0, '-1234,5', -1235,  \NumberFormatter::ROUND_FLOOR],
            [0, '-1234,4', -1235,  \NumberFormatter::ROUND_FLOOR],
            [1,  '123,45',  123.4, \NumberFormatter::ROUND_FLOOR],
            [1,  '123,44',  123.4, \NumberFormatter::ROUND_FLOOR],
            [1, '-123,45', -123.5, \NumberFormatter::ROUND_FLOOR],
            [1, '-123,44', -123.5, \NumberFormatter::ROUND_FLOOR],

            // away from zero (1.6 -> 2, -1.6 -> 2)
            [0,  '1234,5',  1235,  \NumberFormatter::ROUND_UP],
            [0,  '1234,4',  1235,  \NumberFormatter::ROUND_UP],
            [0, '-1234,5', -1235,  \NumberFormatter::ROUND_UP],
            [0, '-1234,4', -1235,  \NumberFormatter::ROUND_UP],
            [1,  '123,45',  123.5, \NumberFormatter::ROUND_UP],
            [1,  '123,44',  123.5, \NumberFormatter::ROUND_UP],
            [1, '-123,45', -123.5, \NumberFormatter::ROUND_UP],
            [1, '-123,44', -123.5, \NumberFormatter::ROUND_UP],

            // towards zero (1.6 -> 1, -1.6 -> -1)
            [0,  '1234,5',  1234,  \NumberFormatter::ROUND_DOWN],
            [0,  '1234,4',  1234,  \NumberFormatter::ROUND_DOWN],
            [0, '-1234,5', -1234,  \NumberFormatter::ROUND_DOWN],
            [0, '-1234,4', -1234,  \NumberFormatter::ROUND_DOWN],
            [1,  '123,45',  123.4, \NumberFormatter::ROUND_DOWN],
            [1,  '123,44',  123.4, \NumberFormatter::ROUND_DOWN],
            [1, '-123,45', -123.4, \NumberFormatter::ROUND_DOWN],
            [1, '-123,44', -123.4, \NumberFormatter::ROUND_DOWN],

            // round halves (.5) to the next even number
            [0,  '1234,6',  1235,  \NumberFormatter::ROUND_HALFEVEN],
            [0,  '1234,5',  1234,  \NumberFormatter::ROUND_HALFEVEN],
            [0,  '1234,4',  1234,  \NumberFormatter::ROUND_HALFEVEN],
            [0,  '1233,5',  1234,  \NumberFormatter::ROUND_HALFEVEN],
            [0,  '1232,5',  1232,  \NumberFormatter::ROUND_HALFEVEN],
            [0, '-1234,6', -1235,  \NumberFormatter::ROUND_HALFEVEN],
            [0, '-1234,5', -1234,  \NumberFormatter::ROUND_HALFEVEN],
            [0, '-1234,4', -1234,  \NumberFormatter::ROUND_HALFEVEN],
            [0, '-1233,5', -1234,  \NumberFormatter::ROUND_HALFEVEN],
            [0, '-1232,5', -1232,  \NumberFormatter::ROUND_HALFEVEN],
            [1,  '123,46',  123.5, \NumberFormatter::ROUND_HALFEVEN],
            [1,  '123,45',  123.4, \NumberFormatter::ROUND_HALFEVEN],
            [1,  '123,44',  123.4, \NumberFormatter::ROUND_HALFEVEN],
            [1,  '123,35',  123.4, \NumberFormatter::ROUND_HALFEVEN],
            [1,  '123,25',  123.2, \NumberFormatter::ROUND_HALFEVEN],
            [1, '-123,46', -123.5, \NumberFormatter::ROUND_HALFEVEN],
            [1, '-123,45', -123.4, \NumberFormatter::ROUND_HALFEVEN],
            [1, '-123,44', -123.4, \NumberFormatter::ROUND_HALFEVEN],
            [1, '-123,35', -123.4, \NumberFormatter::ROUND_HALFEVEN],
            [1, '-123,25', -123.2, \NumberFormatter::ROUND_HALFEVEN],

            // round halves (.5) away from zero
            [0,  '1234,6',  1235,  \NumberFormatter::ROUND_HALFUP],
            [0,  '1234,5',  1235,  \NumberFormatter::ROUND_HALFUP],
            [0,  '1234,4',  1234,  \NumberFormatter::ROUND_HALFUP],
            [0, '-1234,6', -1235,  \NumberFormatter::ROUND_HALFUP],
            [0, '-1234,5', -1235,  \NumberFormatter::ROUND_HALFUP],
            [0, '-1234,4', -1234,  \NumberFormatter::ROUND_HALFUP],
            [1,  '123,46',  123.5, \NumberFormatter::ROUND_HALFUP],
            [1,  '123,45',  123.5, \NumberFormatter::ROUND_HALFUP],
            [1,  '123,44',  123.4, \NumberFormatter::ROUND_HALFUP],
            [1, '-123,46', -123.5, \NumberFormatter::ROUND_HALFUP],
            [1, '-123,45', -123.5, \NumberFormatter::ROUND_HALFUP],
            [1, '-123,44', -123.4, \NumberFormatter::ROUND_HALFUP],

            // round halves (.5) towards zero
            [0,  '1234,6',  1235,  \NumberFormatter::ROUND_HALFDOWN],
            [0,  '1234,5',  1234,  \NumberFormatter::ROUND_HALFDOWN],
            [0,  '1234,4',  1234,  \NumberFormatter::ROUND_HALFDOWN],
            [0, '-1234,6', -1235,  \NumberFormatter::ROUND_HALFDOWN],
            [0, '-1234,5', -1234,  \NumberFormatter::ROUND_HALFDOWN],
            [0, '-1234,4', -1234,  \NumberFormatter::ROUND_HALFDOWN],
            [1,  '123,46',  123.5, \NumberFormatter::ROUND_HALFDOWN],
            [1,  '123,45',  123.4, \NumberFormatter::ROUND_HALFDOWN],
            [1,  '123,44',  123.4, \NumberFormatter::ROUND_HALFDOWN],
            [1, '-123,46', -123.5, \NumberFormatter::ROUND_HALFDOWN],
            [1, '-123,45', -123.4, \NumberFormatter::ROUND_HALFDOWN],
            [1, '-123,44', -123.4, \NumberFormatter::ROUND_HALFDOWN],
        ];
    }

    public function testTransformDoesNotRoundIfNoPrecision()
    {
        $transformer = new LocalizedStringToNumberTransformer(null, null, null, \NumberFormatter::ROUND_DOWN);

        $this->assertEquals(1234.547, $transformer->transform('1234,547'));
    }

    public function testDecimalSeparatorMayBeDotIfGroupingSeparatorIsNotDot()
    {
        $transformer = new LocalizedStringToNumberTransformer('fr', null, true);

        // completely valid format
        $this->assertEquals(1234.5, $transformer->transform('1 234,5'));
        // accept dots
        $this->assertEquals(1234.5, $transformer->transform('1 234.5'));
        // omit group separator
        $this->assertEquals(1234.5, $transformer->transform('1234,5'));
        $this->assertEquals(1234.5, $transformer->transform('1234.5'));
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testDecimalSeparatorMayNotBeDotIfGroupingSeparatorIsDot()
    {
        $transformer = new LocalizedStringToNumberTransformer(null, null, true);

        $transformer->transform('1.234.5');
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testDecimalSeparatorMayNotBeDotIfGroupingSeparatorIsDotWithNoGroupSep()
    {
        $transformer = new LocalizedStringToNumberTransformer(null, null, true);

        $transformer->transform('1234.5');
    }

    public function testDecimalSeparatorMayBeDotIfGroupingSeparatorIsDotButNoGroupingUsed()
    {
        $transformer = new LocalizedStringToNumberTransformer('fr');

        $this->assertEquals(1234.5, $transformer->transform('1234,5'));
        $this->assertEquals(1234.5, $transformer->transform('1234.5'));
    }

    public function testDecimalSeparatorMayBeCommaIfGroupingSeparatorIsNotComma()
    {
        $transformer = new LocalizedStringToNumberTransformer('bg', null, true);

        // completely valid format
        $this->assertEquals(1234.5, $transformer->transform('1 234.5'));
        // accept commas
        $this->assertEquals(1234.5, $transformer->transform('1 234,5'));
        // omit group separator
        $this->assertEquals(1234.5, $transformer->transform('1234.5'));
        $this->assertEquals(1234.5, $transformer->transform('1234,5'));
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testDecimalSeparatorMayNotBeCommaIfGroupingSeparatorIsComma()
    {
        $transformer = new LocalizedStringToNumberTransformer('en', null, true);

        $transformer->transform('1,234,5');
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testDecimalSeparatorMayNotBeCommaIfGroupingSeparatorIsCommaWithNoGroupSep()
    {
        $transformer = new LocalizedStringToNumberTransformer('en', null, true);

        $transformer->transform('1234,5');
    }

    public function testDecimalSeparatorMayBeCommaIfGroupingSeparatorIsCommaButNoGroupingUsed()
    {
        $transformer = new LocalizedStringToNumberTransformer('en');

        $this->assertEquals(1234.5, $transformer->transform('1234,5'));
        $this->assertEquals(1234.5, $transformer->transform('1234.5'));
    }

    public function testTransformEmptyValue()
    {
        $this->assertNull((new LocalizedStringToNumberTransformer())->transform(''));
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testTransformNaN()
    {
        (new LocalizedStringToNumberTransformer())->transform('NaN');
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testTransformDisallowsInfinity()
    {
        (new LocalizedStringToNumberTransformer())->transform('∞');
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testTransformDisallowsNegativeInfinity()
    {
        (new LocalizedStringToNumberTransformer())->transform('-∞');
    }

    /**
     * @dataProvider      getInvalidTransformationData
     * @expectedException \TreeHouse\Feeder\Exception\TransformationFailedException
     */
    public function testTransformationFailure($value)
    {
        $transformer = new LocalizedStringToNumberTransformer(null, null, true);
        $transformer->transform($value);
    }

    public function getInvalidTransformationData()
    {
        return [
            [[]],
            [true],
            ['foo123'],
            ['123foo'],
            ['12foo3'],
            ['12ë3'],
            ["12\xc2\xa0345,678foo"],
            ["12\xc2\xa0345,67foo8"],
            ["12\xc2\xa0345,67foo8  \xc2\xa0\t"],
        ];
    }
}
