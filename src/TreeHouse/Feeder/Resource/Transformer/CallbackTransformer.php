<?php

namespace TreeHouse\Feeder\Resource\Transformer;

use TreeHouse\Feeder\Exception\UnexpectedTypeException;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;

/**
 * Uses a callback to transform a resource.
 */
class CallbackTransformer implements ResourceTransformerInterface
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
    public function transform(ResourceInterface $resource, ResourceCollection $collection)
    {
        return call_user_func($this->callback, $resource, $collection);
    }

    /**
     * @inheritdoc
     */
    public function needsTransforming(ResourceInterface $resource)
    {
        return true;
    }
}
