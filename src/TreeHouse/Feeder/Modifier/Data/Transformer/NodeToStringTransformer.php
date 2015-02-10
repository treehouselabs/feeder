<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

class NodeToStringTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        // if value is an array with a hash, that's a serialized node's text value
        if (is_array($value) && array_key_exists('#', $value)) {
            return $value['#'];
        }

        return $value;
    }
}
