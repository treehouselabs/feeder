<?php

namespace TreeHouse\Feeder\Resource;

use TreeHouse\Feeder\Resource\Transformer\ResourceTransformerInterface;

class ResourceCollection extends \SplQueue
{
    /**
     * @var ResourceTransformerInterface[]
     */
    protected $transformers = [];

    /**
     * @var array
     */
    protected $transformed = [];

    /**
     * @param ResourceInterface[] $resources
     */
    public function __construct(array $resources = [])
    {
        $this->enqueueAll($resources);

        $this->rewind();
    }

    /**
     * @return ResourceInterface
     */
    public function current()
    {
        $resource = parent::current();

        return $resource ? $this->transform($resource) : $resource;
    }

    /**
     * @return ResourceInterface
     */
    public function shift()
    {
        return $this->transform(parent::shift());
    }

    /**
     * @return ResourceInterface
     */
    public function pop()
    {
        return $this->transform(parent::pop());
    }

    /**
     * @return ResourceInterface
     */
    public function dequeue()
    {
        return $this->transform(parent::dequeue());
    }

    /**
     * @return ResourceInterface
     */
    public function bottom()
    {
        return $this->transform(parent::bottom());
    }

    /**
     * @return ResourceInterface
     */
    public function top()
    {
        return $this->transform(parent::top());
    }

    /**
     * @param integer $index
     *
     * @return ResourceInterface
     */
    public function offsetGet($index)
    {
        return $this->transform(parent::offsetGet($index));
    }

    /**
     * @param ResourceInterface[] $resources
     */
    public function enqueueAll(array $resources)
    {
        foreach ($resources as $resource) {
            $this->enqueue($resource);
        }
    }

    /**
     * @param ResourceInterface[] $resources
     */
    public function unshiftAll(array $resources)
    {
        foreach (array_reverse($resources) as $resource) {
            $this->unshift($resource);
        }
    }

    /**
     * @param ResourceTransformerInterface $transformer
     */
    public function addTransformer(ResourceTransformerInterface $transformer)
    {
        $this->transformers[] = $transformer;
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return ResourceInterface
     */
    protected function transform(ResourceInterface $resource)
    {
        $hash = spl_object_hash($resource);

        // see if it needs transforming
        if (!in_array($hash, $this->transformed)) {
            foreach ($this->transformers as $transformer) {
                if ($transformer->needsTransforming($resource)) {
                    $resource = $transformer->transform($resource, $this);
                }
            }

            $this->transformed[] = $hash;
        }

        return $resource;
    }
}
