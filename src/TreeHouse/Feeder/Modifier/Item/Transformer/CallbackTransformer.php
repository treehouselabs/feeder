<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\UnexpectedTypeException;

class CallbackTransformer implements TransformerInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
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
    public function transform(ParameterBag $item)
    {
        call_user_func($this->callback, $item);
    }
}
