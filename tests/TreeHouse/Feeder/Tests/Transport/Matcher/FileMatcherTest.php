<?php

namespace TreeHouse\Feeder\Tests\Transport\Matcher;

use TreeHouse\Feeder\Transport\Matcher\FileMatcher;

class FileMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $matcher = new FileMatcher('foo');
        $this->assertSame('foo', $matcher->match(['bar', 'foo']));
    }

    public function testNoMatch()
    {
        $matcher = new FileMatcher('foo');
        $this->assertNull($matcher->match(['bar', 'baz']));
    }

    public function testToString()
    {
        $matcher = new FileMatcher('foo');
        $this->assertSame('foo', (string) $matcher);
    }
}
