<?php

namespace TreeHouse\Feeder\Modifier\Item\Validator;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\UnexpectedTypeException;

class CallbackValidator implements ValidatorInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * Constructor.
     *
     * @param callable $callback
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new UnexpectedTypeException($callback, 'callback');
        }

        $this->callback = $callback;
    }

    public function validate(ParameterBag $item)
    {
        return call_user_func($this->callback, $item);
    }
}
