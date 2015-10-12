<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\TransformationFailedException;
use TreeHouse\Feeder\Exception\UnexpectedTypeException;

/**
 * Transforms between a normalized time and a localized time string.
 *
 * Copied from Symfony's Form component
 */
class StringToDateTimeTransformer extends AbstractDateTimeTransformer
{
    /**
     * @var string
     */
    protected $format;

    /**
     * @param string $format         The date format
     * @param string $inputTimezone  The name of the input timezone
     * @param string $outputTimezone The name of the output timezone
     * @param bool   $resetFields    Whether to reset date/time fields that are not defined in the format
     *
     * @throws UnexpectedTypeException
     * @throws \InvalidArgumentException
     */
    public function __construct($format = 'Y-m-d H:i:s', $inputTimezone = null, $outputTimezone = null, $resetFields = true)
    {
        parent::__construct($inputTimezone, $outputTimezone);

        /*
         * The character "|" in the format makes sure that the parts of a date
         * that are *not* specified in the format are reset to the corresponding
         * values from 1970-01-01 00:00:00 instead of the current time.
         *
         * @see http://php.net/manual/en/datetime.createfromformat.php
         */
        if ($resetFields && false === strpos($format, '|')) {
            $format .= '|';
        }

        $this->format = $format;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

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

        try {
            $outputTz = new \DateTimeZone($this->outputTimezone);
            $dateTime = \DateTime::createFromFormat($this->format, $value, $outputTz);

            $lastErrors = \DateTime::getLastErrors();

            if (0 < $lastErrors['warning_count'] || 0 < $lastErrors['error_count']) {
                throw new TransformationFailedException(
                    implode(', ', array_merge(
                        array_values($lastErrors['warnings']),
                        array_values($lastErrors['errors'])
                    ))
                );
            }
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        return $dateTime;
    }
}
