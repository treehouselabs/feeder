<?php

namespace TreeHouse\Feeder\Tests\Transport\Matcher;

use TreeHouse\Feeder\Transport\Matcher\PatternMatcher;

class PatternMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $matcher = new PatternMatcher('#foo#');
        $this->assertSame('foo', $matcher->match(['bar', 'foo']));

        $matcher = new PatternMatcher('#foo.*#');
        $this->assertSame('foobar', $matcher->match(['bar', 'foobar']));
    }

    public function testMatchFirstResult()
    {
        $matcher = new PatternMatcher('#foo.*#');
        $this->assertSame('foobar', $matcher->match(['bar', 'foobar', 'foobaz']));
    }

    public function testNoMatch()
    {
        $matcher = new PatternMatcher('#foo#');
        $this->assertNull($matcher->match(['bar', 'baz']));
    }

    public function testToString()
    {
        $matcher = new PatternMatcher('#foo#');
        $this->assertSame('#foo#', (string) $matcher);
    }
}
