<?php

namespace TreeHouse\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;

class FetchProgressEvent extends Event
{
    /**
     * The number of bytes fetched so far.
     *
     * @var integer
     */
    protected $bytesFetched;

    /**
     * The number of bytes to fetch in total.
     *
     * @var integer
     */
    protected $bytesTotal;

    /**
     * @param integer $fetched
     * @param integer $total
     */
    public function __construct($fetched, $total)
    {
        $this->bytesFetched = $fetched;
        $this->bytesTotal   = $total;
    }

    /**
     * Returns the total number of bytes fetched so far.
     *
     * @return integer
     */
    public function getBytesFetched()
    {
        return $this->bytesFetched;
    }

    /**
     * Returns the total number of bytes to be fetched
     *
     * @return integer
     */
    public function getBytesTotal()
    {
        return $this->bytesTotal;
    }
}
