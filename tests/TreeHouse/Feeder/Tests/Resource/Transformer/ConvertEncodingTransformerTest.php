<?php

namespace TreeHouse\Feeder\Tests\Resource\Transformer;

use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\StringResource;
use TreeHouse\Feeder\Resource\Transformer\ConvertEncodingTransformer;

class ConvertEncodingTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('iconv')) {
            $this->markTestSkipped('The iconv extension is not available');
        }
    }

    /**
     * @dataProvider getEncodingTestData
     */
    public function testConvertEncoding($fromEncoding, $toEncoding)
    {
        // this is UTF-8
        $from = 'This is á test';
        $to   = $from;

        if ($fromEncoding !== 'UTF-8') {
            $from = iconv('UTF-8', $fromEncoding, $from);
        }

        if ($toEncoding !== 'UTF-8') {
            $to = iconv('UTF-8', $toEncoding, $to);
        }

        $transformer = new ConvertEncodingTransformer($fromEncoding, $toEncoding);
        $collection = new ResourceCollection();

        $fromResource = new StringResource($from);
        $toResource = $transformer->transform($fromResource, $collection);

        $this->assertSame($to, file_get_contents($toResource->getFile()->getPathname()));
    }

    public static function getEncodingTestData()
    {
        return [
            ['iso-8859-1', 'UTF-8'],
            ['windows-1252', 'UTF-8'],
            ['UTF-8', 'UCS-2LE'],
        ];
    }

    public function testSkipEncoding()
    {
        $from = 'This is á test';
        $transformer = new ConvertEncodingTransformer('ISO-8859-1', 'UTF-8');

        $this->assertFalse($transformer->needsTransforming(new StringResource($from)));
    }
}
