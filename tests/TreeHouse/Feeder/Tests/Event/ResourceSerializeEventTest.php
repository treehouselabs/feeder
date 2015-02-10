<?php

namespace TreeHouse\Feeder\Tests\Event;

use TreeHouse\Feeder\Event\ResourceSerializeEvent;
use TreeHouse\Feeder\Resource\StringResource;

class ResourceSerializeEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $item = '<?xml version="1.0"?><foo><bar></bar></foo>';

    /**
     * @var array
     */
    protected static $serialized = ['foo' => 'bar'];

    public function testEvent()
    {
        $item     = static::$item;
        $resource = new StringResource($item);
        $event    = new ResourceSerializeEvent($resource, $item);

        $this->assertEquals($resource, $event->getResource());

        $resource2 = new StringResource($item);
        $event->setResource($resource2);
        $this->assertEquals($resource, $event->getResource());
    }

    public function testChangeOriginalItemModifiesItem()
    {
        $item = static::$item;
        $event = new ResourceSerializeEvent(new StringResource($item), $item);

        // overwrite $item
        $item = static::$serialized;

        $this->assertEquals($item, $event->getItem());
        $this->assertEquals(static::$serialized, $event->getItem());
    }

    public function testChangeReturnValueModifiesItem()
    {
        $item = static::$item;
        $event = new ResourceSerializeEvent(new StringResource($item), $item);
        $item = &$event->getItem();
        $item = static::$serialized;
        $this->assertEquals(static::$serialized, $event->getItem());
    }

    public function testSetItemModifiesItem()
    {
        $item = static::$item;
        $serialized = static::$serialized;
        $event = new ResourceSerializeEvent(new StringResource($item), $item);
        $event->setItem($serialized);
        $this->assertEquals(static::$serialized, $event->getItem());
        $this->assertEquals(static::$serialized, $item);
    }
}
