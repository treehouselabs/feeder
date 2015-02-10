<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

/**
 * Transforms empty values to null. This is not the exact equivalent of the
 * empty() function. The difference is that this transformer will leave 0 and
 * false alone, as they could be valid feed values.
 */
class EmptyValueToNullTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        // let booleans, integers and floats pass
        if (is_null($value) || is_bool($value) || is_integer($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            if (trim($value) === '') {
                return;
            }
        } elseif (empty($value)) {
            return;
        }

        return $value;
    }
}
