<?php

namespace TreeHouse\Feeder\Tests\Event;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Event\ItemModificationEvent;

class ItemModificationEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $item = new ParameterBag();
        $event = new ItemModificationEvent($item);

        $this->assertInstanceOf(ItemModificationEvent::class, $event);
        $this->assertSame($item, $event->getItem());
    }
}
