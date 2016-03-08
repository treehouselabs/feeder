<?php

namespace TreeHouse\Feeder\Resource\Transformer;

use TreeHouse\Feeder\Reader\ReaderInterface;
use TreeHouse\Feeder\Resource\FileResource;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;
use TreeHouse\Feeder\Transport\FileTransport;
use TreeHouse\Feeder\Writer\WriterInterface;

class MultiPartTransformer implements ResourceTransformerInterface
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int|null
     */
    protected $maxParts;

    /**
     * @param ReaderInterface $reader
     * @param WriterInterface $writer
     * @param int             $size
     * @param int             $maxParts
     */
    public function __construct(ReaderInterface $reader, WriterInterface $writer, $size = 1000, $maxParts = null)
    {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->size = $size;
        $this->maxParts = $maxParts;
    }

    /**
     * @inheritdoc
     */
    public function transform(ResourceInterface $resource, ResourceCollection $collection)
    {
        // break up again
        $files = $this->breakup($resource);

        $resources = [];
        foreach ($files as $file) {
            $transport = FileTransport::create($file);
            $transport->setDestination($file);
            $resources[] = new FileResource($transport);

            if (($this->maxParts > 0) && (sizeof($resources) >= $this->maxParts)) {
                break;
            }
        }

        $collection->unshiftAll($resources);

        return $collection->shift();
    }

    /**
     * @inheritdoc
     */
    public function needsTransforming(ResourceInterface $resource)
    {
        return !$this->isPartFile($resource);
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return int
     */
    protected function isPartFile(ResourceInterface $resource)
    {
        return preg_match('/\.part(\d+)$/', $resource->getFile()->getBasename());
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return array
     */
    protected function getPartFiles(ResourceInterface $resource)
    {
        $files = [];

        $originalFile = $resource->getFile();
        $regex = sprintf('/^%s\.part(\d+)$/', preg_quote($originalFile->getBasename(), '/'));
        $finder = new \DirectoryIterator($originalFile->getPath());

        /** @var $file \SplFileInfo */
        foreach ($finder as $file) {
            if ($file->isFile() && preg_match($regex, $file->getBaseName(), $matches)) {
                $files[(int) $matches[1]] = $file->getPathname();
            }
        }

        ksort($files);

        return $files;
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return array
     */
    protected function breakup(ResourceInterface $resource)
    {
        $originalFile = $resource->getFile();
        $baseFile = $originalFile->getPathname();

        $this->reader->setResources(new ResourceCollection([$resource]));

        $partCount = 0;
        $started = false;
        while ($this->reader->valid()) {
            if ($this->reader->key() % $this->size === 0) {
                if ($this->reader->key() > 0) {
                    $this->endPart();
                }

                $file = sprintf('%s.part%s', $baseFile, ++$partCount);
                $this->startPart($file);
                $started = true;
            }

            $this->writer->write($this->reader->current());
            $this->writer->flush();

            $this->reader->next();
        }
        
        if ($started) {
            $this->endPart();
        }

        return $this->getPartFiles($resource);
    }

    /**
     * @param string $file
     */
    protected function startPart($file)
    {
        $this->writer = clone($this->writer);
        $this->writer->setFile(new \SplFileObject($file, 'w'));
        $this->writer->start();
    }

    /**
     */
    protected function endPart()
    {
        $this->writer->end();
    }
}
