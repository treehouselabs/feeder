<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\UnexpectedTypeException;

abstract class AbstractDateTimeTransformer implements TransformerInterface
{
    /**
     * The name of the input timezone.
     *
     * @var string
     */
    protected $inputTimezone;

    /**
     * The name of the output timezone.
     *
     * @var string
     */
    protected $outputTimezone;

    /**
     * @param string $inputTimezone  The name of the input timezone
     * @param string $outputTimezone The name of the output timezone
     *
     * @throws UnexpectedTypeException   If a timezone is not a string
     * @throws \InvalidArgumentException If a timezone is invalid
     */
    public function __construct($inputTimezone = null, $outputTimezone = null)
    {
        $timeZones = [
            'inputTimezone' => $inputTimezone,
            'outputTimezone' => $outputTimezone,
        ];

        foreach ($timeZones as $field => $timezone) {
            if (!is_string($timezone) && null !== $timezone) {
                throw new UnexpectedTypeException($timezone, 'string');
            }

            $timezone = $timezone ?: date_default_timezone_get();

            // Check if input and output timezones are valid
            try {
                new \DateTimeZone($timezone);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf('%s is invalid: %s.', $field, $timezone), $e->getCode(), $e);
            }

            $this->$field = $timezone;
        }
    }
}
