<?php

namespace TreeHouse\Feeder\Tests\Event;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Event\ItemNotModifiedEvent;

class ItemNotModifiedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $item = new ParameterBag();
        $reason = 'not updated';

        $event = new ItemNotModifiedEvent($item, $reason);

        $this->assertInstanceOf(ItemNotModifiedEvent::class, $event);
        $this->assertSame($item, $event->getItem());
        $this->assertSame($reason, $event->getReason());
    }
}
