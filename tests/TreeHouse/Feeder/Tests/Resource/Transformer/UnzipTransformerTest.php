<?php

namespace TreeHouse\Feeder\Tests\Resource\Transformer;

use TreeHouse\Feeder\Resource\FileResource;
use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;
use TreeHouse\Feeder\Resource\Transformer\ResourceTransformerInterface;
use TreeHouse\Feeder\Resource\Transformer\UnzipTransformer;
use TreeHouse\Feeder\Transport\FileTransport;

class UnzipTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $zipFilename = 'test.zip';

    /**
     * @var string
     */
    protected $filename = 'test.txt';

    /**
     * @var string
     */
    protected $fileContents = 'This is only a test';

    /**
     * @var \ZipArchive
     */
    protected $zipFile;

    /**
     * @var ResourceCollection
     */
    protected $collection;

    protected function setUp()
    {
        if (!extension_loaded('zip')) {
            $this->markTestSkipped('The zip extension is not available');
        }

        $this->zipFile = new \ZipArchive();
        $this->zipFile->open($this->zipFilename, \ZipArchive::CREATE);
        $this->zipFile->addFromString($this->filename, $this->fileContents);
        $this->zipFile->close();

        $this->collection = new ResourceCollection(
            [new FileResource(FileTransport::create($this->zipFilename))]
        );

    }

    protected function tearDown()
    {
        $files = [
            $this->zipFilename,
            $this->filename,
            __DIR__ . '/' . $this->filename
        ];

        // clean up when you're done
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Tests various constructions of the transformer
     */
    public function testConstructor()
    {
        // single file
        $transformer = new UnzipTransformer($this->filename);
        $this->assertInstanceOf(ResourceTransformerInterface::class, $transformer);

        // array of files
        $transformer = new UnzipTransformer([$this->filename]);
        $this->assertInstanceOf(ResourceTransformerInterface::class, $transformer);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConstruction()
    {
        new UnzipTransformer(1234);
    }

    /**
     * Tests the basic transformer functionality
     */
    public function testUnzip()
    {
        $this->collection->addTransformer(new UnzipTransformer([$this->filename]));

        // Loop through the collection instead of using the count() method,
        // because the transformation could change the actual count.
        $count = 0;
        foreach ($this->collection as $resource) {
            // We should get here only once, for the unzipped file. Any other iteration will fail here
            $count++;

            $this->assertSame($this->filename, $resource->getFile()->getBasename());
            $this->assertSame($this->fileContents, $resource->getFile()->fgets());
        }

        $this->assertSame(1, $count);
    }

    /**
     * Tests extraction to different target directory
     */
    public function testUnzipToTarget()
    {
        $this->collection->addTransformer(new UnzipTransformer([$this->filename], __DIR__));

        /** @var ResourceInterface $resource */
        $resource = $this->collection->shift();

        $this->assertSame(__DIR__, $resource->getTransport()->getDestinationDir());
    }

    /**
     * Tests that unzipping is skipped when target exists
     */
    public function testSkipUnzip()
    {
        $transformer = $this
            ->getMockBuilder(UnzipTransformer::class)
            ->setMethods(['unzip'])
            ->setConstructorArgs([[$this->filename], __DIR__])
            ->getMock()
        ;

        $this->collection->addTransformer($transformer);

        // put the to be extracted file in the target dir
        file_put_contents(__DIR__ . '/' . $this->filename, $this->fileContents);

        // unzip should not be called when transforming
        $transformer->expects($this->never())->method('unzip');

        $this->collection->shift();
    }

    /**
     * Tests that unzipping is not skipped when target exists but is older than zip
     */
    public function testDontSkipUnzip()
    {
        $transformer = $this
            ->getMockBuilder(UnzipTransformer::class)
            ->setMethods(['unzip'])
            ->setConstructorArgs([[$this->filename], __DIR__])
            ->getMock()
        ;

        $this->collection->addTransformer($transformer);

        // put the to be extracted file in the target dir
        file_put_contents(__DIR__ . '/' . $this->filename, $this->fileContents);

        // wait 1 second and touch the zip file
        sleep(1);
        touch($this->zipFilename);

        // unzip should be called when transforming
        $transformer->expects($this->once())->method('unzip');

        $this->collection->shift();
    }
}
