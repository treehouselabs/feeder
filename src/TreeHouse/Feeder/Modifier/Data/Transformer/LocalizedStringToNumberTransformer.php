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
     * @var integer
     */
    protected $type;

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
     * Constructor
     *
     * @param integer $type
     * @param integer $precision
     * @param boolean $grouping
     * @param integer $roundingMode
     * @param string  $locale
     */
    public function __construct(
        $type = \NumberFormatter::TYPE_DOUBLE,
        $precision = null,
        $grouping = null,
        $roundingMode = null,
        $locale = null
    ) {
        $this->type = $type;

        if (null === $grouping) {
            $grouping = false;
        }

        if (null === $roundingMode) {
            $roundingMode = \NumberFormatter::ROUND_HALFUP;
        }

        if (null === $locale) {
            $locale = \Locale::getDefault();
        }

        $this->precision    = $precision;
        $this->grouping     = $grouping;
        $this->roundingMode = $roundingMode;
        $this->locale       = $locale;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (is_scalar($value)) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
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

        $result = $formatter->parse($value, $this->type, $position);

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new TransformationFailedException($formatter->getErrorMessage());
        }

        if ($result >= PHP_INT_MAX || $result <= -PHP_INT_MAX) {
            throw new TransformationFailedException('I don\'t have a clear idea what infinity looks like');
        }

        $encoding = mb_detect_encoding($value);
        $length = mb_strlen($value, $encoding);

        // After parsing, position holds the index of the character where the
        // parsing stopped
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

        return $result;
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
}
