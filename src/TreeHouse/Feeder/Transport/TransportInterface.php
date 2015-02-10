<?php

namespace TreeHouse\Feeder\Transport;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface TransportInterface
{
    /**
     * @return string
     */
    public function getDestination();

    /**
     * @return string
     */
    public function getDestinationDir();

    /**
     * @return \DateTime|null
     */
    public function getLastModifiedDate();

    /**
     * @return integer|null
     */
    public function getSize();

    /**
     * @return \SplFileObject
     */
    public function getFile();

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();

    /**
     * Purges a previously transported file, removing the destination and
     * whatever cache the transport uses internally
     */
    public function purge();

    /**
     * @return string
     */
    public function __toString();
}
