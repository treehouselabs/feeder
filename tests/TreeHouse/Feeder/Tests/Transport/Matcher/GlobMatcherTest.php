<?php

namespace TreeHouse\Feeder\Tests\Transport\Matcher;

use TreeHouse\Feeder\Transport\Matcher\GlobMatcher;

class GlobMatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $matcher = new GlobMatcher('foo*baz');
        $this->assertSame('foobarbaz', $matcher->match(['bar', 'foobarbaz']));

        $matcher = new GlobMatcher('foo/*/baz');
        $this->assertSame('foo/bar/baz', $matcher->match(['bar', 'foo/bar/baz']));
    }

    public function testNoMatch()
    {
        $matcher = new GlobMatcher('foo/*/baz');
        $this->assertNull($matcher->match(['bar', 'baz']));
    }

    public function testToString()
    {
        $matcher = new GlobMatcher('foo*baz');
        $this->assertSame('foo*baz', (string) $matcher);
    }
}
