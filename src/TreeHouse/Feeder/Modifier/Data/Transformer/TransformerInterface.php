<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\TransformationFailedException;

interface TransformerInterface
{
    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws TransformationFailedException
     */
    public function transform($value);
}
