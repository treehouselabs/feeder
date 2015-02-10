<?php

namespace TreeHouse\Feeder\Tests\Event;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Event\InvalidItemEvent;

class InvalidItemEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $item   = new ParameterBag();
        $reason = 'because';

        $event = new InvalidItemEvent($item, $reason);

        $this->assertInstanceOf(InvalidItemEvent::class, $event);
        $this->assertSame($item, $event->getItem());
        $this->assertSame($reason, $event->getReason());
    }
}
