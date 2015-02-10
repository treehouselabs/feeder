<?php

namespace TreeHouse\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

class InvalidItemEvent extends Event
{
    /**
     * @var ParameterBag
     */
    protected $item;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @param ParameterBag $item
     * @param string       $reason
     */
    public function __construct(ParameterBag $item, $reason)
    {
        $this->item   = $item;
        $this->reason = $reason;
    }

    /**
     * @return ParameterBag
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
