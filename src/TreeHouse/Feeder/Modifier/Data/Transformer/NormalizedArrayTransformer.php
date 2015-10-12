<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\TransformationFailedException;

class NormalizedArrayTransformer implements TransformerInterface
{
    /**
     * @var bool
     */
    protected $nestedArrays;

    /**
     * @param bool $nestedArrays
     */
    public function __construct($nestedArrays = false)
    {
        $this->nestedArrays = $nestedArrays;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_scalar($value)) {
            $value = [$value];
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(
                sprintf('Expected a scalar value or array to transform, got "%s" instead.', json_encode($value))
            );
        }

        if ($this->nestedArrays && !is_numeric(key($value))) {
            $value = [$value];
        }

        return $value;
    }
}
