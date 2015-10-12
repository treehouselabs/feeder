<?php

namespace TreeHouse\Feeder\Tests\Resource\Transformer;

use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\StringResource;
use TreeHouse\Feeder\Resource\Transformer\OverwriteXmlDeclarationTransformer;

class OverwriteXmlDeclarationTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testTransform($from, $to)
    {
        $resource = new StringResource($from);
        $collection = new ResourceCollection([$resource]);

        $transformer = new OverwriteXmlDeclarationTransformer();

        $resource = $transformer->transform($resource, $collection);
        $file = $resource->getFile()->getPathname();

        $this->assertSame($to, file_get_contents($file));
    }

    public static function getTestData()
    {
        return [
            [
                '<?xml version="1.0" encoding="ISO-8859-1"?>
                <root></root>',
                '<?xml version="1.0" encoding="UTF-8"?>
                <root></root>',
            ],
            [
                '<root></root>',
                '<root></root>',
            ],
            [
                '<?xml version="1.0" encoding="UTF-16"?>
                <root></root>',
                '<?xml version="1.0" encoding="UTF-8"?>
                <root></root>',
            ],
        ];
    }
}
