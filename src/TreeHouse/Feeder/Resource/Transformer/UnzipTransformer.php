<?php

namespace TreeHouse\Feeder\Resource\Transformer;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use TreeHouse\Feeder\Exception\TransportException;
use TreeHouse\Feeder\Resource\FileResource;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;
use TreeHouse\Feeder\Transport\FileTransport;

class UnzipTransformer implements ResourceTransformerInterface
{
    /**
     * @var string
     */
    protected $target;

    /**
     * @var string[]
     */
    protected $files;

    /**
     * @param string|array $files  The filename(s) in the zip file to return
     * @param string       $target Target directory, defaults to the directory in which the zip is located
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($files, $target = null)
    {
        if (is_string($files)) {
            $files = [$files];
        }

        if (!is_array($files)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expecting a file path or array of file paths as first argument, got "%s"',
                    json_encode($files)
                )
            );
        }

        $this->files  = $files;
        $this->target = $target;
    }

    /**
     * @inheritdoc
     */
    public function transform(ResourceInterface $resource, ResourceCollection $collection)
    {
        if ($this->needsUnzipping($resource)) {
            $this->unzip($resource);
        }

        $resources = [];
        foreach ($this->files as $file) {
            $targetFile = $this->getTargetFile($resource, $file);
            if (!file_exists($targetFile)) {
                throw new TransportException(sprintf('File "%s" was not found in the archive', $targetFile));
            }

            $transport = FileTransport::create($targetFile);
            $transport->setDestinationDir($this->getTargetDir($resource));
            $resources[] = new FileResource($transport);
        }

        $collection->unshiftAll($resources);

        return $collection->shift();
    }

    /**
     * @inheritdoc
     */
    public function needsTransforming(ResourceInterface $resource)
    {
        $resourceFile = (string) $resource->getTransport();

        // don't transform unzipped files
        foreach ($this->files as $file) {
            if ($resourceFile === $this->getTargetFile($resource, $file)) {
                return false;
            }
        }

        // check if file type is actually zip
        return $this->isExtractable($resource);
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return boolean
     */
    protected function needsUnzipping(ResourceInterface $resource)
    {
        foreach ($this->files as $file) {
            $targetFile = $this->getTargetFile($resource, $file);
            if (!file_exists($targetFile) || ($resource->getFile()->getMTime() > filemtime($targetFile))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return boolean
     */
    protected function isExtractable(ResourceInterface $resource)
    {
        $guesser = MimeTypeGuesser::getInstance();

        return $guesser->guess($resource->getFile()->getPathname()) === 'application/zip';
    }

    /**
     * @param ResourceInterface $resource
     */
    protected function unzip(ResourceInterface $resource)
    {
        $zip = new \ZipArchive();
        $zip->open($resource->getFile()->getPathname());
        $zip->extractTo($this->getTargetDir($resource));
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return string
     */
    protected function getTargetDir(ResourceInterface $resource)
    {
        return $this->target ?: $resource->getFile()->getPath();
    }

    /**
     * @param ResourceInterface $resource
     * @param string            $filename
     *
     * @return string
     */
    protected function getTargetFile(ResourceInterface $resource, $filename)
    {
        return sprintf('%s/%s', $this->getTargetDir($resource), $filename);
    }
}
