<?php

namespace TreeHouse\Feeder\Tests\Reader;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Reader\JsonLinesReader;
use TreeHouse\Feeder\Resource\StringResource;

class JsonLinesReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testReadJsonLines()
    {
        $reader = new JsonLinesReader(
            new StringResource("{\"a\": 1}\n{\"b\": 2}\n")
        );

        $this->assertEquals(new ParameterBag(['a' => 1]), $reader->read());
        $this->assertEquals(new ParameterBag(['b' => 2]), $reader->read());
        $this->assertEquals(null, $reader->read());
    }

    public function testReturnsNullWhenEmpty()
    {
        $reader = new JsonLinesReader(
            new StringResource("")
        );

        $this->assertEquals(null, $reader->read());
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\ReadException
     */
    public function testThrowsExceptionWhenInvalidJson()
    {
        $reader = new JsonLinesReader(
            new StringResource("{a:1}")
        );

        $reader->read();
    }
}
