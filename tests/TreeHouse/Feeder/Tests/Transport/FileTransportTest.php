<?php

namespace TreeHouse\Feeder\Tests\Transport;

use TreeHouse\Feeder\Transport\Connection;
use TreeHouse\Feeder\Transport\FileTransport;

class FileTransportTest extends AbstractTransportTest
{
    public function testConstructor()
    {
        $transport = new FileTransport(new Connection(['file' => __FILE__]));

        $this->assertInstanceOf(FileTransport::class, $transport);
    }

    public function testFactory()
    {
        $transport = FileTransport::create(__FILE__);

        $this->assertInstanceOf(FileTransport::class, $transport);
        $this->assertEquals(basename(__FILE__), $transport->getFilename());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConnection()
    {
        new FileTransport(new Connection([]));
    }

    /**
     * @expectedException \TreeHouse\Feeder\Exception\TransportException
     */
    public function testInvalidFile()
    {
        FileTransport::create('/foo/bar');
    }

    public function testFileAttributes()
    {
        $filename = $this->getFilename();
        $transport = FileTransport::create($filename);

        $this->assertEquals(basename($filename), $transport->getFilename());
        $this->assertEquals(filesize($filename), $transport->getSize());
        $this->assertEquals(new \DateTime('@' . filemtime($filename)), $transport->getLastModifiedDate());
    }

    public function testDefaultDestination()
    {
        $filename = $this->getFilename();
        $transport = FileTransport::create($filename);
        $transport->setDestinationDir(sys_get_temp_dir());

        $this->assertEquals(sys_get_temp_dir() . '/' . basename($filename), $transport->getDefaultDestination());
    }

    public function testDefaultDestinationSameDir()
    {
        $filename = $this->getFilename();
        $transport = FileTransport::create($filename);
        $transport->setDestinationDir(dirname($filename));

        $this->assertEquals($filename, $transport->getDefaultDestination(), 'When destination directory is the same, use the original file');
    }

    public function testToString()
    {
        $transport = $this->getTransport();

        $this->assertEquals($this->getFilename(), (string) $transport);
    }

    /**
     * @return FileTransport
     */
    protected function getTransport()
    {
        return FileTransport::create($this->getFilename());
    }

    /**
     * @return string
     */
    private function getFilename()
    {
        return __FILE__;
    }
}
