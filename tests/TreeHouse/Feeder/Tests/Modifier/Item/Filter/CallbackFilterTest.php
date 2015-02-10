<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\FilterException;
use TreeHouse\Feeder\Modifier\Item\Filter\CallbackFilter;

class CallbackFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $filter = new CallbackFilter(function () {});

        $this->assertInstanceOf(CallbackFilter::class, $filter);
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\UnexpectedTypeException
     */
    public function testInvalidConstructor()
    {
        new CallbackFilter(true);
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\FilterException
     */
    public function testFilter()
    {
        $filter = new CallbackFilter(function () {
            throw new FilterException();
        });

        $filter->filter(new ParameterBag());
    }
}
