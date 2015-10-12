<?php

namespace TreeHouse\Feeder\Resource\Transformer;

use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;

interface ResourceTransformerInterface
{
    /**
     * @param ResourceInterface  $resource
     * @param ResourceCollection $collection
     *
     * @return ResourceInterface
     */
    public function transform(ResourceInterface $resource, ResourceCollection $collection);

    /**
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    public function needsTransforming(ResourceInterface $resource);
}
