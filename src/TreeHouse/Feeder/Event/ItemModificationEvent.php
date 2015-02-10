<?php

namespace TreeHouse\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

class ItemModificationEvent extends Event
{
    /**
     * @var ParameterBag
     */
    protected $item;

    /**
     * @param ParameterBag $item
     */
    public function __construct(ParameterBag $item)
    {
        $this->item = $item;
    }

    /**
     * @return ParameterBag
     */
    public function getItem()
    {
        return $this->item;
    }
}
