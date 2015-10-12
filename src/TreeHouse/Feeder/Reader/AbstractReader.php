<?php

namespace TreeHouse\Feeder\Reader;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Event\ResourceEvent;
use TreeHouse\Feeder\Event\ResourceSerializeEvent;
use TreeHouse\Feeder\FeedEvents;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;

abstract class AbstractReader implements ReaderInterface
{
    /**
     * @var ResourceCollection
     */
    protected $resources;

    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var bool
     */
    protected $initialized;

    /**
     * @param mixed                    $resources  Optional resource collection. Can be a Resource, an array of
     *                                             Resource's, or a ResourceCollection. When empty, a new collection
     *                                             will be created.
     * @param EventDispatcherInterface $dispatcher Optional event dispatcher.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($resources = null, EventDispatcherInterface $dispatcher = null)
    {
        if ($resources instanceof ResourceInterface) {
            $resources = [$resources];
        }

        if (is_array($resources)) {
            $resources = new ResourceCollection($resources);
        }

        if ($resources === null) {
            $resources = new ResourceCollection();
        }

        if (!$resources instanceof ResourceCollection) {
            throw new \InvalidArgumentException(
                'Second argument must be a Resource object, an array of Resource objects, or null'
            );
        }

        $this->resources = $resources;
        $this->eventDispatcher = $dispatcher ?: new EventDispatcher();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        $this->initialize();

        return $this->doCurrent();
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        $this->initialize();

        return $this->doKey();
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->initialize();

        $this->doNext();

        // if the current reader is not valid, create a reader for the next resource
        if (!$this->valid()) {
            $this->createNextReader();
        }
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->initialize();

        $this->doRewind();
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        $this->initialize();

        return $this->doValid();
    }

    /**
     * Wrapper that implements various calls, so you can use the iterator in a
     * simple while loop.
     *
     * @return ParameterBag
     */
    public function read()
    {
        if (!$this->valid()) {
            return null;
        }

        // keep a local copy of the resource; the next() call could change the cached one
        $resource = $this->resource;

        $item = $this->current();
        $this->next();

        // serialize the item
        $event = new ResourceSerializeEvent($resource, $item);
        $this->eventDispatcher->dispatch(FeedEvents::RESOURCE_PRE_SERIALIZE, $event);
        $item = $this->serialize($item);
        $this->eventDispatcher->dispatch(FeedEvents::RESOURCE_POST_SERIALIZE, $event);

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function setResources(ResourceCollection $resources)
    {
        $this->resources = $resources;

        // must reinitialize, because we basically start over at this point
        $this->initialized = false;
    }

    /**
     * @inheritdoc
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return ResourceInterface
     */
    public function getCurrentResource()
    {
        return $this->resource;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     *
     */
    protected function createNextReader()
    {
        if ($this->resource) {
            // end existing resource first
            $this->eventDispatcher->dispatch(
                FeedEvents::RESOURCE_END,
                new ResourceEvent($this->resource, $this->resources)
            );
        }

        if ($this->resources->isEmpty()) {
            return;
        }

        // get the next resource
        $this->resource = $this->resources->shift();

        // dispatch start event
        $this->eventDispatcher->dispatch(
            FeedEvents::RESOURCE_START,
            new ResourceEvent($this->resource, $this->resources)
        );

        // create a reader for this new resource
        $this->createReader($this->resource);
    }

    /**
     *
     */
    protected function initialize()
    {
        if ($this->initialized) {
            return;
        }

        // mark initialized first, to prevent recursive calls
        $this->initialized = true;

        $this->resources->rewind();
        $this->createNextReader();
    }

    /**
     * Serializes a read item into a ParameterBag.
     *
     * @param string $data
     *
     * @return ParameterBag
     */
    abstract protected function serialize($data);

    /**
     * Creates a reader for a resource.
     *
     * @param ResourceInterface $resource
     */
    abstract protected function createReader(ResourceInterface $resource);

    /**
     * @see \Iterator::key()
     */
    abstract protected function doKey();

    /**
     * @see \Iterator::current()
     */
    abstract protected function doCurrent();

    /**
     * @see \Iterator::next()
     */
    abstract protected function doNext();

    /**
     * @see \Iterator::valid()
     */
    abstract protected function doValid();

    /**
     * @see \Iterator::rewind()
     */
    abstract protected function doRewind();
}
