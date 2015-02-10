<?php

namespace TreeHouse\Feeder\Tests\Transport;

use Symfony\Component\EventDispatcher\EventDispatcher;
use TreeHouse\Feeder\Tests\Mock\TransportMock;
use TreeHouse\Feeder\Transport\Connection;
use TreeHouse\Feeder\Transport\TransportInterface;

abstract class AbstractTransportTest extends \PHPUnit_Framework_TestCase
{
    public function testConnection()
    {
        $transport = new TransportMock();

        $conn = new Connection(['foo' => 'bar']);
        $transport->setConnection($conn);

        $this->assertSame($conn, $transport->getConnection());
    }

    public function testEventDispatcher()
    {
        $transport = new TransportMock();

        $dispatcher = new EventDispatcher();
        $transport->setEventDispatcher($dispatcher);

        $this->assertSame($dispatcher, $transport->getEventDispatcher());
    }

    public function testMaxAge()
    {
        $transport = new TransportMock();

        $age = 3600;
        $transport->setMaxAge($age);

        $this->assertSame($age, $transport->getMaxAge());
    }

    public function testDestination()
    {
        $transport = new TransportMock();

        $destination = '/tmp/feeder';
        $transport->setDestination($destination);

        $this->assertSame($destination, $transport->getDestination());
    }

    /**
     * @expectedException \LogicException
     */
    public function testDestinationImmutable()
    {
        $transport = new TransportMock();
        $transport->setDestination('/tmp/feeder');
        $transport->setDestination('/foo');
    }

    public function testDestinationDir()
    {
        $transport = new TransportMock();

        $dir = '/tmp/';
        $transport->setDestinationDir($dir);

        $this->assertSame($dir, $transport->getDestinationDir());
    }

    /**
     * @expectedException \LogicException
     */
    public function testDestinationDirImmutable()
    {
        $transport = new TransportMock();
        $transport->setDestination('/tmp/feeder');
        $transport->setDestinationDir('/foo');
    }

    public function testDefaultDestinationDir()
    {
        $transport = new TransportMock();
        $this->assertNotEmpty($transport->getDestinationDir());
    }

    public function testDefaultDestination()
    {
        $transport = new TransportMock();
        $this->assertNotEmpty($transport->getDefaultDestination());
    }

    public function testDefaultUserAgent()
    {
        $transport = new TransportMock();
        $this->assertNotEmpty($transport->getDefaultUserAgent());
    }

    public function testStringCast()
    {
        $transport = $this->getTransport();

        $this->assertInternalType('string', (string) $transport);
    }

    public function testFetch()
    {
        $transport = $this->getTransport();
        $file = $transport->getFile();

        $this->assertInstanceOf(\SplFileObject::class, $file);
        $this->assertGreaterThan(0, $file->getSize());
    }

    public function testPurge()
    {
        $transport = $this->getTransport();
        $file      = $transport->getFile();
        $filename  = $file->getPathname();

        $this->assertTrue(file_exists($filename));

        $transport->purge();

        $this->assertFalse(file_exists($filename));
    }

    /**
     * @return TransportInterface
     */
    abstract protected function getTransport();
}
