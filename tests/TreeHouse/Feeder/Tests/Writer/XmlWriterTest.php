<?php

namespace TreeHouse\Feeder\Tests\Writer;

use TreeHouse\Feeder\Resource\TempFile;
use TreeHouse\Feeder\Writer\XmlWriter;

class XmlWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testWriter()
    {
        $file   = new TempFile();
        $writer = new XmlWriter($file);

        $this->assertInstanceOf(XmlWriter::class, $writer);

        $writer->start();
        $writer->write('<foo>Bar</foo>');
        $writer->end();

        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed><foo>Bar</foo></feed>

XML
            ,
            file_get_contents($file->getPathname())
        );
    }

    public function testRootNode()
    {
        $file   = new TempFile();
        $writer = new XmlWriter($file);

        $writer->setRootNode('foo');
        $writer->start();
        $writer->write('<bar>Baz</bar>');
        $writer->end();

        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<foo><bar>Baz</bar></foo>

XML
            ,
            file_get_contents($file->getPathname())
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testStartWithoutFile()
    {
        $writer = new XmlWriter();
        $writer->start();
    }

    /**
     * @expectedException \LogicException
     */
    public function testStartTwice()
    {
        $writer = new XmlWriter(new TempFile());
        $writer->start();
        $writer->start();
    }

    /**
     * @expectedException \LogicException
     */
    public function testWriteWithoutStart()
    {
        $writer = new XmlWriter(new TempFile());
        $writer->write('<foo>Bar</foo>');
    }

    /**
     * @expectedException \LogicException
     */
    public function testFlushWithoutStart()
    {
        $writer = new XmlWriter(new TempFile());
        $writer->flush();
    }

    /**
     * @expectedException \LogicException
     */
    public function testEndWithoutStart()
    {
        $writer = new XmlWriter(new TempFile());
        $writer->end();
    }
}
