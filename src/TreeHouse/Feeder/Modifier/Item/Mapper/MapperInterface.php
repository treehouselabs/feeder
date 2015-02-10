<?php

namespace TreeHouse\Feeder\Modifier\Item\Mapper;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\ModifierInterface;

interface MapperInterface extends ModifierInterface
{
    /**
     * @param ParameterBag $item
     *
     * @return ParameterBag
     */
    public function map(ParameterBag $item);
}
