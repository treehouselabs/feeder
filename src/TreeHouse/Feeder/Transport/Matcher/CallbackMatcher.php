<?php

namespace TreeHouse\Feeder\Transport\Matcher;

use TreeHouse\Feeder\Exception\UnexpectedTypeException;

class CallbackMatcher implements MatcherInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * Constructor
     *
     * @param  callable                $callback
     * @throws UnexpectedTypeException
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new UnexpectedTypeException($callback, 'callback');
        }

        $this->callback = $callback;
    }

    public function match(array $files)
    {
        return call_user_func($this->callback, $files);
    }

    public function __toString()
    {
        return 'callback';
    }
}
