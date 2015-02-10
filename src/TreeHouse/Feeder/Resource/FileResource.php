<?php

namespace TreeHouse\Feeder\Resource;

use TreeHouse\Feeder\Exception\TransportException;
use TreeHouse\Feeder\Transport\TransportInterface;

class FileResource implements ResourceInterface
{
    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @inheritdoc
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @inheritdoc
     */
    public function setFile(\SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function getFile()
    {
        if ($this->file === null) {
            try {
                $this->file = $this->transport->getFile();
            } catch (\RuntimeException $e) {
                throw new TransportException(
                    sprintf('Could not open file "%s": %s', $this->transport->getDestination(), $e->getMessage()),
                    null,
                    $e
                );
            }
        }

        return $this->file;
    }
}
