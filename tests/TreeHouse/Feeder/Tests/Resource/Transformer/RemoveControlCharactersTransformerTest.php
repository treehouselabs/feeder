<?php

namespace TreeHouse\Feeder\Tests\Resource\Transformer;

use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\StringResource;
use TreeHouse\Feeder\Resource\Transformer\RemoveControlCharactersTransformer;

class RemoveControlCharactersTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestData
     */
    public function testTransform($from, $to)
    {
        $resource   = new StringResource($from);
        $collection = new ResourceCollection([$resource]);

        $transformer = new RemoveControlCharactersTransformer(1);

        $resource = $transformer->transform($resource, $collection);
        $file     = $resource->getFile()->getPathname();

        $this->assertSame($to, file_get_contents($file));
    }

    public static function getTestData()
    {
        return [
            [
                sprintf('Stripping null bytes%s, unit separators%s, and vertical tabs%s', chr(0), chr(31), chr(11)),
                'Stripping null bytes, unit separators, and vertical tabs'
            ],
            [
                sprintf("While stripping control%s characters%s...\n\nnewlines and\ttabs are kept intact", chr(26), chr(127)),
                "While stripping control characters...\n\nnewlines and\ttabs are kept intact"
            ],
            [
                sprintf("Also%s, mültîb¥†é characters%s do not break", chr(7), chr(27)),
                "Also, mültîb¥†é characters do not break"
            ]
        ];
    }
}
