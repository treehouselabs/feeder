<?php

namespace TreeHouse\Feeder\Transport;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TreeHouse\Feeder\Event\TransportEvent;
use TreeHouse\Feeder\Exception\TransportException;
use TreeHouse\Feeder\FeedEvents;

abstract class AbstractTransport implements TransportInterface
{
    /**
     * File where transport will download to. The file name is generated if this is used.
     *
     * @var string
     *
     * @private There's some logic here that requires only this class has access to it.
     */
    private $destination;

    /**
     * Directory where transport will download to. The file name is generated if this is used.
     *
     * @var string
     */
    protected $destinationDir;

    /**
     * The number of seconds that the transport may be cached
     *
     * @var integer
     */
    protected $maxAge;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param Connection               $conn
     * @param string|null              $destination
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(Connection $conn, $destination = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->connection      = $conn;
        $this->destination     = $destination;
        $this->eventDispatcher = $dispatcher ?: new EventDispatcher();
        $this->maxAge          = 86400;
    }

    /**
     * @inheritdoc
     */
    public function __clone()
    {
        $this->destination = null;
        $this->connection  = clone $this->connection;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->connection->__toString();
    }

    /**
     * @inheritdoc
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @inheritdoc
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * @inheritdoc
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param integer $seconds
     */
    public function setMaxAge($seconds)
    {
        $this->maxAge = $seconds;
    }

    /**
     * @return integer
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * @inheritdoc
     */
    public function setDestination($destination)
    {
        if ($this->destination) {
            throw new \LogicException(
                'Destination is already set and is immutable. If you want to
                change the destination, you can clone this transport or create
                a new one'
            );
        }

        $this->destination = $destination;
    }

    /**
     * @inheritdoc
     */
    public function getDestination()
    {
        if (!$this->destination) {
            $this->destination = $this->getDefaultDestination();
        }

        return $this->destination;
    }

    /**
     * @inheritdoc
     */
    public function setDestinationDir($destinationDir)
    {
        if ($this->destination) {
            throw new \LogicException(
                'Destination is already set and is immutable. If you want to
                change the destination directory, you can clone this transport
                or create a new one'
            );
        }

        $this->destinationDir = $destinationDir;
    }

    /**
     * @inheritdoc
     */
    public function getDestinationDir()
    {
        return $this->destinationDir ?: sys_get_temp_dir();
    }

    /**
     * @return string
     */
    public function getDefaultDestination()
    {
        return sprintf('%s/%s', rtrim($this->getDestinationDir(), '/'), $this->connection->getHash());
    }

    /**
     * @inheritdoc
     */
    public function getFile()
    {
        $maxAge = new \DateTime();
        $maxAge->sub(new \DateInterval(sprintf('PT%dS', $this->maxAge)));

        return new \SplFileObject($this->fetch($maxAge));
    }

    /**
     * @param \DateTime $maxAge
     *
     * @throws TransportException
     *
     * @return string
     */
    final public function fetch(\DateTime $maxAge = null)
    {
        $destination = $this->getDestination();

        // check if we need to download feed
        $event = new TransportEvent($this);
        if (!$this->isFresh($destination, $maxAge)) {
            // make sure directory exists
            $dir = dirname($destination);
            if (!is_dir($dir)) {
                if (true !== @mkdir($dir, 0777, true)) {
                    throw new TransportException(sprintf('Could not create feed dir "%s"', $dir));
                }
            }

            // perform an atomic write operation: first write to a tmp destination, then rename it.
            $tmpDestination = $this->getTempDestination($destination);

            $this->eventDispatcher->dispatch(FeedEvents::PRE_FETCH, $event);

            $this->doFetch($tmpDestination);
            if (false === rename($tmpDestination, $destination)) {
                unlink($tmpDestination);

                throw new TransportException(sprintf('Could not rename "%s" to "%s"', $tmpDestination, $destination));
            }

            $this->eventDispatcher->dispatch(FeedEvents::POST_FETCH, $event);
        } else {
            $this->eventDispatcher->dispatch(FeedEvents::FETCH_CACHED, $event);
        }

        return $destination;
    }

    /**
     * @return string
     */
    final public static function getDefaultUserAgent()
    {
        return 'Feeder/1.0';
    }

    /**
     * Purges a previously transported file, removing the destination and
     * whatever cache the transport uses internally
     *
     * @return void
     */
    public function purge()
    {
        $destination = $this->getDestination();

        if (is_file($destination)) {
            unlink($destination);
        }
    }

    /**
     * @param string $destination
     *
     * @return string
     */
    protected function getTempDestination($destination)
    {
        return tempnam(dirname($destination), basename($destination));
    }

    /**
     * @param string    $destination
     * @param \DateTime $maxAge
     *
     * @return boolean
     */
    protected function isFresh($destination, \DateTime $maxAge = null)
    {
        // fetch if file does not exist
        if (!file_exists($destination)) {
            return false;
        }

        // if file exists and no max-age is given, use the cached file
        if (!$maxAge instanceof \DateTime) {
            return true;
        }

        // download if ttl is passed
        $mtime = new \Datetime('@'.filemtime($destination));

        // see if cache has expired
        if ($mtime < $maxAge) {
            return false;
        }

        // check with last modified date (if available)
        if (($lastMod = $this->getLastModifiedDate()) && ($mtime < $lastMod)) {
            return false;
        }

        // all checks passed, use the cached version
        return true;
    }

    /**
     * Fetches the resource, makes sure a file is present at the given destination
     *
     * @param string $destination
     */
    abstract protected function doFetch($destination);
}
