<?php

namespace TreeHouse\Feeder\Tests\Event;

use TreeHouse\Feeder\Event\ResourceEvent;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\StringResource;

class ResourceEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $resource = new StringResource('foo');
        $collection = new ResourceCollection([$resource]);
        $event = new ResourceEvent($resource, $collection);

        $this->assertInstanceOf(ResourceEvent::class, $event);
        $this->assertSame($resource, $event->getResource());
        $this->assertSame($collection, $event->getResources());
    }
}
