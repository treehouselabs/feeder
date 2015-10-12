<?php

namespace TreeHouse\Feeder\Modifier\Data\Transformer;

use TreeHouse\Feeder\Exception\TransformationFailedException;

interface TransformerInterface
{
    /**
     * @param mixed $value
     *
     * @throws TransformationFailedException
     * @return mixed
     *
     */
    public function transform($value);
}
