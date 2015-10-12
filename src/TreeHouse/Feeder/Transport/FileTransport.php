<?php

namespace TreeHouse\Feeder\Transport;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TreeHouse\Feeder\Exception\TransportException;

class FileTransport extends AbstractTransport
{
    /**
     * @inheritdoc
     */
    public function __construct(Connection $conn, $destination = null, EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct($conn, $destination, $dispatcher);

        if (!isset($this->connection['file'])) {
            throw new \InvalidArgumentException('The "file" key is required in the Connection object');
        }

        if (!is_readable($this->connection['file'])) {
            throw new TransportException(sprintf('Not readable: %s', $this->connection['file']));
        }
    }

    /**
     * Factory method.
     *
     * @param string $file
     *
     * @return FileTransport
     */
    public static function create($file)
    {
        return new self(new Connection(['file' => $file]));
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string) $this->connection['file'];
    }

    /**
     * @inheritdoc
     */
    public function getLastModifiedDate()
    {
        return new \DateTime('@' . filemtime($this->connection['file']));
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return filesize($this->connection['file']);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return basename($this->connection['file']);
    }

    /**
     * @return string
     */
    public function getDefaultDestination()
    {
        // if the destination dir is not set or the same, use the original file
        if (!$this->getDestinationDir() || (dirname($this->connection['file']) === $this->getDestinationDir())) {
            return $this->connection['file'];
        }

        // make sure the file is copied to the specified destination
        return sprintf('%s/%s', rtrim($this->getDestinationDir(), '/'), basename($this->connection['file']));
    }

    /**
     * @inheritdoc
     */
    protected function doFetch($destination)
    {
        // the destination may be the same as the source
        if (realpath($this->connection['file']) === realpath($destination)) {
            return;
        }

        copy($this->connection['file'], $destination);
    }
}
