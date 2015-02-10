<?php

namespace TreeHouse\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use TreeHouse\Feeder\Transport\TransportInterface;

class TransportEvent extends Event
{
    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }
}
