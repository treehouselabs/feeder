<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

class InverseBooleanTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (is_null($value) || ($value === '')) {
            return;
        }

        return !(bool) $value;
    }
}
