<?php

namespace TreeHouse\Feeder\Resource\Transformer;

use TreeHouse\Feeder\Resource\ResourceInterface;
use TreeHouse\Feeder\Resource\ResourceCollection;

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
     * @return boolean
     */
    public function needsTransforming(ResourceInterface $resource);
}
