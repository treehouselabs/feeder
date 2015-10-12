<?php

namespace TreeHouse\Feeder\Tests\Resource\Transformer;

use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\StringResource;
use TreeHouse\Feeder\Resource\Transformer\RemoveByteOrderMarkTransformer;

class RemoveByteOrderMarkTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testTransform($bom)
    {
        $to = 'Look ma, no BOM!';
        $from = sprintf('%s%s', pack('H*', $bom), $to);

        $resource = new StringResource($from);
        $collection = new ResourceCollection([$resource]);

        $transformer = new RemoveByteOrderMarkTransformer();

        $resource = $transformer->transform($resource, $collection);
        $file = $resource->getFile()->getPathname();

        $this->assertSame($to, file_get_contents($file));
    }

    public static function getTestData()
    {
        return [
            ['EFBBBF'],   // UTF-8
            ['FEFF'],     // UTF-16 (BE)
            ['FFFE'],     // UTF-16 (LE)
            ['0000FEFF'], // UTF-32 (BE)
            ['FFFE0000'], // UTF-32 (LE)
        ];
    }
}
