<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\TransformationFailedException;

/**
 * Transforms a string to an array, using one or more delimiters.
 */
class EnumeratedStringToArrayTransformer implements TransformerInterface
{
    /**
     * @var array
     */
    protected $delimiters;

    /**
     * @var string
     */
    protected $regex;

    /**
     * @param array $delimiters
     */
    public function __construct(array $delimiters = [])
    {
        $this->delimiters = !empty($delimiters) ? $delimiters : [','];
        $this->regex = sprintf('/(%s)+/', implode('|', array_map(function ($delimiter) {
            if (mb_strlen($delimiter) > 1) {
                // treat it as a word
                return '\b' . preg_quote($delimiter, '/') . '\b';
            }

            return preg_quote($delimiter, '/');
        }, $this->delimiters)));
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        // only transform when we have something to transform
        if (is_null($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value;
        }

        if (!is_scalar($value)) {
            throw new TransformationFailedException(
                sprintf('Expected a scalar value to transform, got %s instead.', var_export($value, true))
            );
        }

        return array_map('trim', preg_split($this->regex, $value, null, PREG_SPLIT_NO_EMPTY));
    }
}
