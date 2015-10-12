<?php

namespace TreeHouse\Feeder\Tests\Resource;

use TreeHouse\Feeder\Resource\FileResource;
use TreeHouse\Feeder\Resource\TempFile;
use TreeHouse\Feeder\Transport\FileTransport;

class FileResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testFileResource()
    {
        $transport = FileTransport::create(__FILE__);
        $resource = new FileResource($transport);

        $this->assertInstanceOf(FileResource::class, $resource);
        $this->assertSame($transport, $resource->getTransport());
        $this->assertInstanceOf(\SplFileObject::class, $resource->getFile());

        $file = new TempFile();
        $resource->setFile($file);
        $this->assertSame($file, $resource->getFile());
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransportException
     */
    public function testNonExistingFileResource()
    {
        $transport = FileTransport::create('/foo');
        $resource = new FileResource($transport);

        $resource->getFile();
    }
}
