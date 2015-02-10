<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

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
     * @param array $delimiters
     */
    public function __construct(array $delimiters = [])
    {
        $this->delimiters = !empty($delimiters) ? $delimiters : [','];
        $this->regex = sprintf('/(%s)+/', implode('|', array_map(function ($delimiter) {
            if (mb_strlen($delimiter) > 1) {
                // treat it as a word
                return '\b'.preg_quote($delimiter, '/').'\b';
            }

            return preg_quote($delimiter, '/');
        }, $this->delimiters)));
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (is_string($value)) {
            return array_map('trim', preg_split($this->regex, $value, null, PREG_SPLIT_NO_EMPTY));
        }

        return $value;
    }
}
