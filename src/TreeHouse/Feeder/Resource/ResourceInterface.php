<?php

namespace TreeHouse\Feeder\Resource;

use TreeHouse\Feeder\Exception\TransportException;
use TreeHouse\Feeder\Transport\TransportInterface;

interface ResourceInterface
{
    /**
     * @return TransportInterface
     */
    public function getTransport();

    /**
     * @return \SplFileObject
     *
     * @throws TransportException
     */
    public function getFile();

    /**
     * @param $file \SplFileObject
     */
    public function setFile(\SplFileObject $file);
}
