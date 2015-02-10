<?php

namespace TreeHouse\Feeder\Modifier\Item\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\UnexpectedTypeException;

class CallbackFilter implements FilterInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * Constructor
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

    /**
     * @inheritdoc
     */
    public function filter(ParameterBag $item)
    {
        return call_user_func($this->callback, $item);
    }
}
