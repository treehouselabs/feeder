<?php

namespace TreeHouse\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use TreeHouse\Feeder\Resource\ResourceInterface;

class ResourceSerializeEvent extends Event
{
    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $item;

    /**
     * @param ResourceInterface $resource
     * @param mixed             $item
     */
    public function __construct(ResourceInterface $resource, &$item)
    {
        $this->resource = $resource;
        $this->item     = &$item;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param ResourceInterface $resource
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function &getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }
}
