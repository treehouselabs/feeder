<?php

namespace TreeHouse\Feeder\Tests\Reader;

use TreeHouse\Feeder\Reader\CsvReader;
use TreeHouse\Feeder\Resource\StringResource;

class CsvReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testReadCsv()
    {
        $reader = new CsvReader(new StringResource('foo,bar,baz'));
        $bag = $reader->read();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\ParameterBag', $bag);

        foreach (['foo', 'bar', 'baz'] as $num => $test) {
            $this->assertSame($test, $bag->get($num));
        }

        // no more items left
        $this->assertNull($reader->read());
    }

    public function testNewlineAtEof()
    {
        $reader = new CsvReader(new StringResource(<<<CSV
foo,bar,baz

CSV
        ));

        // if newlines are not properly trimmed, this will fatal
        while ($item = $reader->read()) {
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\ParameterBag', $item);
        }
    }

    public function testMapping()
    {
        $reader = new CsvReader(new StringResource('foo,bar,baz'));
        $reader->setFieldMapping(['field1', 'field2', 'field3']);

        $bag = $reader->read();
        $this->assertSame('foo', $bag->get('field1'));
        $this->assertSame('bar', $bag->get('field2'));
        $this->assertSame('baz', $bag->get('field3'));
    }

    public function testFirstRowMapping()
    {
        $reader = new CsvReader(new StringResource(<<<CSV
field1,field2,field3
foo,bar,baz
CSV
        ));

        $reader->useFirstRow(true);

        $bag = $reader->read();
        $this->assertSame('foo', $bag->get('field1'));
        $this->assertSame('bar', $bag->get('field2'));
        $this->assertSame('baz', $bag->get('field3'));
    }

    public function testDelimiter()
    {
        $reader = new CsvReader(new StringResource('foo;bar;baz'));
        $reader->setDelimiter(';');

        $bag = $reader->read();
        $this->assertSame('foo', $bag->get(0));
        $this->assertSame('bar', $bag->get(1));
        $this->assertSame('baz', $bag->get(2));
    }

    public function testEnclosure()
    {
        $reader = new CsvReader(new StringResource("foo,'bar, baz',qux"));
        $reader->setEnclosure("'");

        $bag = $reader->read();
        $this->assertSame('foo', $bag->get(0));
        $this->assertSame('bar, baz', $bag->get(1));
        $this->assertSame('qux', $bag->get(2));
    }

    public function testEscape()
    {
        $reader = new CsvReader(new StringResource('"foo","air ""quotes"""'));
        $reader->setEscape('"');

        $bag = $reader->read();
        $this->assertSame('air "quotes"', $bag->get(1));
    }

    public function testConvertNull()
    {
        $reader = new CsvReader(new StringResource('foo,null'));
        $bag = $reader->read();
        $this->assertSame('null', $bag->get(1));

        $reader = new CsvReader(new StringResource('foo,NULL,null'));
        $reader->convertNull(true);

        $bag = $reader->read();
        $this->assertSame(null, $bag->get(1));
        $this->assertSame(null, $bag->get(2));
    }
}
