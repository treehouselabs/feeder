<?php

namespace TreeHouse\Feeder\Resource\Transformer;

use TreeHouse\Feeder\Resource\FileResource;
use TreeHouse\Feeder\Resource\ResourceInterface;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Transport\FileTransport;

/**
 * Converts character encoding
 */
class ConvertEncodingTransformer implements ResourceTransformerInterface
{
    /**
     * @var string
     */
    protected $fromEncoding;

    /**
     * @var string
     */
    protected $toEncoding;

    /**
     * @param string $fromEncoding The encoding in which the resource is initially
     * @param string $toEncoding   The encoding to convert to. Uses internal encoding when left empty
     */
    public function __construct($fromEncoding, $toEncoding = null)
    {
        $this->fromEncoding = $fromEncoding;
        $this->toEncoding   = $toEncoding ?: mb_internal_encoding();
    }

    /**
     * @inheritdoc
     */
    public function transform(ResourceInterface $resource, ResourceCollection $collection)
    {
        $file = $resource->getFile()->getPathname();

        // first, rename the original file
        $oldFile = $this->rename($file);

        $old = fopen($oldFile, 'r');
        $new = fopen($file, 'w');
        while (!feof($old)) {
            fwrite($new, mb_convert_encoding(fgets($old), $this->toEncoding, $this->fromEncoding));
        }

        fclose($old);
        fclose($new);

        unlink($oldFile);

        $transport = FileTransport::create($file);

        if ($resource->getTransport()) {
            $transport->setDestinationDir($resource->getTransport()->getDestinationDir());
        }

        return new FileResource($transport);
    }

    /**
     * @inheritdoc
     */
    public function needsTransforming(ResourceInterface $resource)
    {
        $file = $resource->getFile();
        while (!$file->eof()) {
            $line = $file->fgets();
            if (!mb_check_encoding($line, $this->toEncoding)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $file
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function rename($file)
    {
        $tmpFile = $file.'.tmp';

        if (rename($file, $tmpFile)) {
            return $tmpFile;
        }

        throw new \RuntimeException(sprintf('Could not rename %s to %s', $file, $tmpFile));
    }
}
