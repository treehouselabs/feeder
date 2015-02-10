<?php

namespace TreeHouse\Feeder\Tests\Resource;

use TreeHouse\Feeder\Resource\StringResource;
use TreeHouse\Feeder\Resource\TempFile;

class StringResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testFileResource()
    {
        $resource = new StringResource('foo');

        $this->assertInstanceOf(StringResource::class, $resource);
        $this->assertNull($resource->getTransport());
        $this->assertInstanceOf(\SplFileObject::class, $resource->getFile());

        $this->assertSame('foo', $resource->getFile()->fgets());
    }

    /**
     * @dataProvider      getInvalidData
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidData($data)
    {
        new StringResource($data);
    }

    public function getInvalidData()
    {
        return [
            [false],
            [1],
            [[]],
            [new \StdClass()],
        ];
    }

    public function testSetFile()
    {
        $resource = new StringResource('foo');

        $file = new TempFile();
        $resource->setFile($file);
        $this->assertSame($file, $resource->getFile());
    }
}
