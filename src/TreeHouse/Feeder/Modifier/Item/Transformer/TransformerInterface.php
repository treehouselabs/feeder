<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\ModifierInterface;

interface TransformerInterface extends ModifierInterface
{
    /**
     * @param ParameterBag $item
     */
    public function transform(ParameterBag $item);
}
