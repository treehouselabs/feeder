<?php

namespace TreeHouse\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;

class FetchProgressEvent extends Event
{
    /**
     * The number of bytes fetched so far.
     *
     * @var int
     */
    protected $bytesFetched;

    /**
     * The number of bytes to fetch in total.
     *
     * @var int
     */
    protected $bytesTotal;

    /**
     * @param int $fetched
     * @param int $total
     */
    public function __construct($fetched, $total)
    {
        $this->bytesFetched = $fetched;
        $this->bytesTotal = $total;
    }

    /**
     * Returns the total number of bytes fetched so far.
     *
     * @return int
     */
    public function getBytesFetched()
    {
        return $this->bytesFetched;
    }

    /**
     * Returns the total number of bytes to be fetched.
     *
     * @return int
     */
    public function getBytesTotal()
    {
        return $this->bytesTotal;
    }
}
