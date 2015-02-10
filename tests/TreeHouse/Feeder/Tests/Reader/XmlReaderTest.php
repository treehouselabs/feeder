<?php

namespace TreeHouse\Feeder\Tests\Reader;

use TreeHouse\Feeder\Reader\XmlReader;
use TreeHouse\Feeder\Resource\StringResource;

class XmlReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCurrentXml()
    {
        $reader = new XmlReader(new StringResource('<foo><bar>baz</bar></foo>'));
        $reader->setNodeCallback('bar');

        $this->assertSame('<bar>baz</bar>', $reader->current());

        return $reader;
    }

    public function testReadXml()
    {
        $reader = new XmlReader(new StringResource('<foo><test>foo</test><test>bar</test><test>baz</test></foo>'));
        $reader->setNodeCallback('test');

        foreach (['foo', 'bar', 'baz'] as $test) {
            $bag = $reader->read();
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\ParameterBag', $bag);
            $this->assertSame($test, $bag->get(0));
        }

        // no more items left
        $this->assertNull($reader->read());
    }

    /**
     * @expectedException        \TreeHouse\Feeder\Exception\ReadException
     * @expectedExceptionMessage Opening and ending tag mismatch
     */
    public function testCurrentOnInvalidXml()
    {
        $reader = new XmlReader(new StringResource('<foo><baz/><bar></foo>'));
        $reader->setNodeCallback('bar');
        $reader->current();
    }

    /**
     * @expectedException        \TreeHouse\Feeder\Exception\ReadException
     * @expectedExceptionMessage Opening and ending tag mismatch
     */
    public function testReadOnInvalidXml()
    {
        $reader = new XmlReader(new StringResource('<foo><bar/><bar></foo>'));
        $reader->setNodeCallback('bar');
        $reader->read();
    }

    /**
     * @expectedException        \TreeHouse\Feeder\Exception\ReadException
     * @expectedExceptionMessage Opening and ending tag mismatch
     */
    public function testNextOnInvalidXml()
    {
        $reader = new XmlReader(new StringResource('<foo><bar></foo>'));
        $reader->setNodeCallback('bar');
        $reader->next();
    }
}
