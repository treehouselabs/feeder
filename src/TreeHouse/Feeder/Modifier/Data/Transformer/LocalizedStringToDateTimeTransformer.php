<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\TransformationFailedException;
use TreeHouse\Feeder\Exception\UnexpectedTypeException;

/**
 * Transforms between a normalized time and a localized time string
 *
 * Copied from Symfony's Form component
 */
class LocalizedStringToDateTimeTransformer extends DateTimeTransformer
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var integer
     */
    protected $dateFormat;

    /**
     * @var integer
     */
    protected $timeFormat;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var integer
     */
    protected $calendar;

    /**
     * Constructor.
     *
     * @see DateTimeTransformer::formats for available format options
     *
     * @param string  $locale
     * @param string  $inputTimezone  The name of the input timezone
     * @param string  $outputTimezone The name of the output timezone
     * @param integer $dateFormat     The date format; one of the \IntlDateFormatter date constants
     * @param integer $timeFormat     The time format; one of the \IntlDateFormatter date constants
     * @param integer $calendar       One of the \IntlDateFormatter calendar constants
     * @param string  $pattern        A pattern to pass to \IntlDateFormatter
     *
     * @throws UnexpectedTypeException If a format is not supported or if a timezone is not a string
     */
    public function __construct(
        $locale = null,
        $inputTimezone = null,
        $outputTimezone = null,
        $dateFormat = null,
        $timeFormat = null,
        $calendar = \IntlDateFormatter::GREGORIAN,
        $pattern = null
    ) {
        parent::__construct($inputTimezone, $outputTimezone);

        if (null === $locale) {
            $locale = \Locale::getDefault();
        }

        if (null === $dateFormat) {
            $dateFormat = \IntlDateFormatter::MEDIUM;
        }

        if (null === $timeFormat) {
            $timeFormat = \IntlDateFormatter::SHORT;
        }

        if (!in_array($dateFormat, self::$formats, true)) {
            throw new UnexpectedTypeException($dateFormat, implode('", "', self::$formats));
        }

        if (!in_array($timeFormat, self::$formats, true)) {
            throw new UnexpectedTypeException($timeFormat, implode('", "', self::$formats));
        }

        $this->locale     = $locale;
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
        $this->calendar   = $calendar;
        $this->pattern    = $pattern;
    }

    /**
     * Transforms a localized date string/array into a normalized date.
     *
     * @param mixed $value
     *
     * @throws TransformationFailedException
     *
     * @return \DateTime Normalized date
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
            return;
        }

        $timestamp = $this->getIntlDateFormatter()->parse($value);

        if (intl_get_error_code() != 0) {
            throw new TransformationFailedException(intl_get_error_message());
        }

        try {
            // read timestamp into DateTime object - the formatter delivers in UTC
            $dateTime = new \DateTime(sprintf('@%s UTC', $timestamp));
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        if ('UTC' !== $this->inputTimezone) {
            try {
                $dateTime->setTimezone(new \DateTimeZone($this->inputTimezone));
            } catch (\Exception $e) {
                throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $dateTime;
    }

    /**
     * Returns a preconfigured IntlDateFormatter instance
     *
     * @return \IntlDateFormatter
     */
    protected function getIntlDateFormatter()
    {
        $locale     = $this->locale;
        $dateFormat = $this->dateFormat;
        $timeFormat = $this->timeFormat;
        $timezone   = $this->outputTimezone;
        $calendar   = $this->calendar;
        $pattern    = $this->pattern;

        $intlDateFormatter = new \IntlDateFormatter($locale, $dateFormat, $timeFormat, $timezone, $calendar, $pattern);
        $intlDateFormatter->setLenient(false);

        return $intlDateFormatter;
    }
}
