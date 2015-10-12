<?php

namespace TreeHouse\Feeder\Resource\Transformer;

use TreeHouse\Feeder\Resource\FileResource;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;
use TreeHouse\Feeder\Transport\FileTransport;

/**
 * Replaces xml declaration with a fixed one.
 */
class OverwriteXmlDeclarationTransformer implements ResourceTransformerInterface
{
    /**
     * @var string
     */
    protected $xmlDeclaration;

    /**
     * @var string regexp
     */
    protected $xmlDeclarationRegEx = '/^\<\?xml.*\?\>/i';

    /**
     * @param string $xmlDeclaration
     */
    public function __construct($xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>')
    {
        $this->xmlDeclaration = $xmlDeclaration;
    }

    /**
     * @inheritdoc
     */
    public function transform(ResourceInterface $resource, ResourceCollection $collection)
    {
        $file = $resource->getFile()->getPathname();

        // the file could be big, so just read the
        $tmpFile = tempnam(sys_get_temp_dir(), $file);
        $old = fopen($file, 'r');
        $new = fopen($tmpFile, 'w');

        // write the beginning with the xml declaration replaced
        fwrite($new, preg_replace($this->xmlDeclarationRegEx, $this->xmlDeclaration, fread($old, 96)));

        // now copy the rest of the file
        while (!feof($old)) {
            fwrite($new, fread($old, 8192));
        }

        fclose($old);
        fclose($new);

        // atomic write
        $this->rename($tmpFile, $file);

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
        $file = $resource->getFile()->getPathname();
        $handle = fopen($file, 'r');

        $result = (bool) preg_match($this->xmlDeclarationRegEx, fread($handle, 96));

        fclose($handle);

        return $result;
    }

    /**
     * @param string $old
     * @param string $new
     *
     * @throws \RuntimeException
     */
    protected function rename($old, $new)
    {
        if (!rename($old, $new)) {
            throw new \RuntimeException(sprintf('Could not rename %s to %s', $old, $new));
        }
    }
}
