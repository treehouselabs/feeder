<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\TransformationFailedException;

/**
 * Transforms between a number type and a localized number with grouping (each thousand) and comma separators.
 *
 * Copied from Symfony's Form component
 */
class LocalizedStringToNumberTransformer implements TransformerInterface
{
    /**
     * Number of fraction digits
     *
     * @var integer
     */
    protected $precision;

    /**
     * Whether to use a grouping separator
     *
     * @var boolean
     */
    protected $grouping;

    /**
     * The rounding mode to use
     *
     * @var integer
     */
    protected $roundingMode;

    /**
     * The locale to use
     *
     * @var string
     */
    protected $locale;

    /**
     * @param integer $precision
     * @param boolean $grouping
     * @param integer $roundingMode
     * @param string  $locale
     */
    public function __construct($locale = null, $precision = null, $grouping = null, $roundingMode = null)
    {
        if (null === $locale) {
            $locale = \Locale::getDefault();
        }

        if (null === $grouping) {
            $grouping = false;
        }

        if (null === $roundingMode) {
            $roundingMode = \NumberFormatter::ROUND_HALFUP;
        }

        $this->locale       = $locale;
        $this->precision    = $precision;
        $this->grouping     = $grouping;
        $this->roundingMode = $roundingMode;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (!is_string($value) && !is_numeric($value)) {
            throw new TransformationFailedException(
                sprintf('Expected a string to transform, got "%s" instead.', json_encode($value))
            );
        }

        if ('' === $value) {
            return null;
        }

        if ('NaN' === $value) {
            throw new TransformationFailedException('"NaN" is not a valid number');
        }

        $position = 0;
        $formatter = $this->getNumberFormatter();
        $groupSep = $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        $decSep = $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        if ('.' !== $decSep && (!$this->grouping || '.' !== $groupSep)) {
            $value = str_replace('.', $decSep, $value);
        }

        if (',' !== $decSep && (!$this->grouping || ',' !== $groupSep)) {
            $value = str_replace(',', $decSep, $value);
        }
        $result = $formatter->parse($value, \NumberFormatter::TYPE_DOUBLE, $position);

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new TransformationFailedException($formatter->getErrorMessage());
        }

        if ($result >= PHP_INT_MAX || $result <= -PHP_INT_MAX) {
            throw new TransformationFailedException('I don\'t have a clear idea what infinity looks like');
        }

        $encoding = mb_detect_encoding($value);
        $length = mb_strlen($value, $encoding);

        // After parsing, position holds the index of the character where the parsing stopped
        if ($position < $length) {
            // Check if there are unrecognized characters at the end of the
            // number (excluding whitespace characters)
            $remainder = trim(mb_substr($value, $position, $length, $encoding), " \t\n\r\0\x0b\xc2\xa0");

            if ('' !== $remainder) {
                throw new TransformationFailedException(
                    sprintf('The number contains unrecognized characters: "%s"', $remainder)
                );
            }
        }

        // Only the format() method in the NumberFormatter rounds, whereas parse() does not
        return $this->round($result);
    }

    /**
     * Returns a preconfigured \NumberFormatter instance
     *
     * @return \NumberFormatter
     */
    protected function getNumberFormatter()
    {
        $formatter = new \NumberFormatter($this->locale, \NumberFormatter::DECIMAL);

        if (null !== $this->precision) {
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->precision);
            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, $this->roundingMode);
        }

        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, $this->grouping);

        return $formatter;
    }

    /**
     * Rounds a number according to the configured precision and rounding mode.
     *
     * @param int|float $number A number.
     *
     * @return int|float The rounded number.
     */
    private function round($number)
    {
        if (null !== $this->precision && null !== $this->roundingMode) {
            // shift number to maintain the correct precision during rounding
            $roundingCoef = pow(10, $this->precision);
            $number *= $roundingCoef;

            switch ($this->roundingMode) {
                case \NumberFormatter::ROUND_CEILING:
                    $number = ceil($number);
                    break;
                case \NumberFormatter::ROUND_FLOOR:
                    $number = floor($number);
                    break;
                case \NumberFormatter::ROUND_UP:
                    $number = $number > 0 ? ceil($number) : floor($number);
                    break;
                case \NumberFormatter::ROUND_DOWN:
                    $number = $number > 0 ? floor($number) : ceil($number);
                    break;
                case \NumberFormatter::ROUND_HALFEVEN:
                    $number = round($number, 0, PHP_ROUND_HALF_EVEN);
                    break;
                case \NumberFormatter::ROUND_HALFUP:
                    $number = round($number, 0, PHP_ROUND_HALF_UP);
                    break;
                case \NumberFormatter::ROUND_HALFDOWN:
                    $number = round($number, 0, PHP_ROUND_HALF_DOWN);
                    break;
            }

            $number /= $roundingCoef;
        }

        return $number;
    }
}
