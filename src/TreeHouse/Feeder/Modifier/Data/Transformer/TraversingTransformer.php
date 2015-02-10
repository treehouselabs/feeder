<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\TransformationFailedException;

class TraversingTransformer implements TransformerInterface
{
    /**
     * Transformer that will be applied to each value
     *
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * @param TransformerInterface $transformer transformer to apply
     */
    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritdoc
     */
    public function transform($value)
    {
        // not an array, must be traversable
        if (!is_array($value) && !$value instanceof \Traversable) {
            throw new TransformationFailedException(
                sprintf('Expected an array or \Traversable to transform, got "%s" instead.', gettype($value))
            );
        }

        // traverse through object, transforming each item
        foreach ($value as &$val) {
            $val = $this->transformer->transform($val);
        }

        return $value;
    }
}
