<?php

namespace TreeHouse\Feeder\Tests\Event;

use TreeHouse\Feeder\Event\TransportEvent;
use TreeHouse\Feeder\Tests\Mock\TransportMock;

class TransportEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $transport = new TransportMock();
        $event = new TransportEvent($transport);

        $this->assertInstanceOf(TransportEvent::class, $event);
        $this->assertSame($transport, $event->getTransport());
    }
}
