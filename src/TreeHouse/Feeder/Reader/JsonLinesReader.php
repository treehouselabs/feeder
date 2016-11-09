<?php

namespace TreeHouse\Feeder\Reader;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\EmptyResponseException;
use TreeHouse\Feeder\Exception\ReadException;
use TreeHouse\Feeder\Resource\ResourceInterface;

class JsonLinesReader extends AbstractReader
{
    /**
     * @var \Iterator
     */
    protected $fileObject;

    /**
     * Serializes a read item into a ParameterBag.
     *
     * @param string $data
     *
     * @return ParameterBag
     *
     * @throws ReadException
     */
    protected function serialize($data)
    {
        if ('' === trim($data)) {
            return null;
        }

        if (null === $result = json_decode($data, true)) {
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ReadException(json_last_error_msg(), json_last_error());
            }
        }

        return new ParameterBag($result);
    }

    /**
     * Creates a reader for a resource.
     *
     * @param ResourceInterface $resource
     */
    protected function createReader(ResourceInterface $resource)
    {
        try {
            $jsonFile = $resource->getFile()->getPathname();

            $this->fileObject = new \SplFileObject($jsonFile);
        } catch (EmptyResponseException $e) {
            // getting an empty response is not a problem for jsonlines files, this
            // just means that there are no records.
            $this->fileObject = new \ArrayIterator();
        }
    }

    /**
     * @inheritdoc
     */
    protected function doKey()
    {
        return $this->fileObject->key();
    }

    /**
     * @inheritdoc
     */
    protected function doCurrent()
    {
        return $this->fileObject->current();
    }

    /**
     * @inheritdoc
     */
    protected function doNext()
    {
        $this->fileObject->next();
    }

    /**
     * @inheritdoc
     */
    protected function doValid()
    {
        return $this->fileObject->valid();
    }

    /**
     * @inheritdoc
     */
    protected function doRewind()
    {
        $this->fileObject->rewind();
    }
}
