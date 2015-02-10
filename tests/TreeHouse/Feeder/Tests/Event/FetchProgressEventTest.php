<?php

namespace TreeHouse\Feeder\Tests\Event;

use TreeHouse\Feeder\Event\FetchProgressEvent;

class FetchProgressEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $event = new FetchProgressEvent(5, 10);

        $this->assertInstanceOf(FetchProgressEvent::class, $event);
        $this->assertSame(5, $event->getBytesFetched());
        $this->assertSame(10, $event->getBytesTotal());
    }
}
