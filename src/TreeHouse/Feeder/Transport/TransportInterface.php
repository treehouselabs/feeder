<?php

namespace TreeHouse\Feeder\Transport;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface TransportInterface
{
    /**
     * @param Connection $connection
     */
    public function setConnection(Connection $connection);

    /**
     * @return Connection
     */
    public function getConnection();

    /**
     * @param string $destination
     *
     * @throws \LogicException When the destination is already set
     */
    public function setDestination($destination);

    /**
     * @return string
     */
    public function getDestination();

    /**
     * @param string $destinationDir
     *
     * @throws \LogicException
     */
    public function setDestinationDir($destinationDir);

    /**
     * @return string
     */
    public function getDestinationDir();

    /**
     * @return \DateTime|null
     */
    public function getLastModifiedDate();

    /**
     * @return int|null
     */
    public function getSize();

    /**
     * @return \SplFileObject
     */
    public function getFile();

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher);

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();

    /**
     * Purges a previously transported file, removing the destination and whatever cache the transport uses internally.
     */
    public function purge();

    /**
     * @return string
     */
    public function __toString();
}
