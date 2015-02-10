<?php

namespace TreeHouse\Feeder\Modifier\Item\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\ModifierInterface;

interface FilterInterface extends ModifierInterface
{
    /**
     * @throws \TreeHouse\Feeder\Exception\FilterException If item needs to be filtered
     */
    public function filter(ParameterBag $item);
}
