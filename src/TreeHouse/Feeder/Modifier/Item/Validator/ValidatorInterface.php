<?php

namespace TreeHouse\Feeder\Modifier\Item\Validator;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\ModifierInterface;

interface ValidatorInterface extends ModifierInterface
{
    /**
     * @throws \TreeHouse\Feeder\Exception\ValidationException If item is invalid
     */
    public function validate(ParameterBag $item);
}
