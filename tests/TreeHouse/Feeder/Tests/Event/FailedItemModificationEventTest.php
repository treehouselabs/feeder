<?php

namespace TreeHouse\Feeder\Tests\Event;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Event\FailedItemModificationEvent;
use TreeHouse\Feeder\Exception\ModificationException;
use TreeHouse\Feeder\Modifier\Item\Transformer\CallbackTransformer;

class FailedItemModificationEventTest extends \PHPUnit_Framework_TestCase
{
    public function testEvent()
    {
        $item = new ParameterBag();
        $modifier = new CallbackTransformer(function () {});
        $exception = new ModificationException();

        $event = new FailedItemModificationEvent($item, $modifier, $exception);

        $this->assertSame($item, $event->getItem());
        $this->assertSame($modifier, $event->getModifier());
        $this->assertSame($exception, $event->getException());

        $this->assertFalse($event->getContinue());
        $event->setContinue(true);
        $this->assertTrue($event->getContinue());
    }
}
