<?php

namespace TreeHouse\Feeder\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\ModificationException;
use TreeHouse\Feeder\Modifier\Item\ModifierInterface;

class FailedItemModificationEvent extends Event
{
    /**
     * @var ParameterBag
     */
    protected $item;

    /**
     * @var ModifierInterface
     */
    protected $modifier;

    /**
     * @var ModificationException
     */
    protected $exception;

    /**
     * @var boolean
     */
    protected $continue = false;

    /**
     * @param ParameterBag          $item
     * @param ModifierInterface     $modifier
     * @param ModificationException $exception
     */
    public function __construct(ParameterBag $item, ModifierInterface $modifier, ModificationException $exception)
    {
        $this->item      = $item;
        $this->modifier  = $modifier;
        $this->exception = $exception;
    }

    /**
     * @return ParameterBag
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return ModifierInterface
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * @return ModificationException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param boolean $bool
     */
    public function setContinue($bool)
    {
        $this->continue = (boolean) $bool;
    }

    /**
     * @return boolean
     */
    public function getContinue()
    {
        return $this->continue;
    }
}
