<?php

namespace TreeHouse\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;

class ResourceEvent extends Event
{
    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var ResourceCollection
     */
    protected $resources;

    /**
     * @param ResourceInterface  $resource
     * @param ResourceCollection $resources
     */
    public function __construct(ResourceInterface $resource, ResourceCollection $resources)
    {
        $this->resource = $resource;
        $this->resources = $resources;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return ResourceCollection
     */
    public function getResources()
    {
        return $this->resources;
    }
}
