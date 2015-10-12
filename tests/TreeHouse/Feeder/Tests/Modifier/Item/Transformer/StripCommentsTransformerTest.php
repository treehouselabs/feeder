<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Transformer;

use TreeHouse\Feeder\Modifier\Item\Transformer\StripCommentsTransformer;
use TreeHouse\Feeder\Reader\XmlReader;
use TreeHouse\Feeder\Resource\StringResource;

class StripCommentsTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestvalues
     */
    public function testTransformer($xml, array $expected)
    {
        $reader = new XmlReader(new StringResource($xml));
        $reader->setNodeCallback('foo');

        $item = $reader->read();

        $transformer = new StripCommentsTransformer();
        $transformer->transform($item);
        $result = $item->all();

        $this->assertSame($result, $expected);
    }

    public static function getTestvalues()
    {
        return [
            [
                <<<XML
<foo>
    <photos>
       <!-- This is a comment -->
       <photo>http://example.org/photo1.jpg</photo>
       <photo>http://example.org/photo2.jpg</photo>
       <photo>http://example.org/photo3.jpg</photo>
    </photos>
</foo>
XML
                ,
                [
                    'photos' => [
                        'photo' => [
                            0 => 'http://example.org/photo1.jpg',
                            1 => 'http://example.org/photo2.jpg',
                            2 => 'http://example.org/photo3.jpg',
                        ],
                    ],
                ],
            ],
            [
                <<<XML
<foo>
    <photos>
       <!-- This is a comment, no nodes though -->
    </photos>
</foo>
XML
                ,
                [
                    'photos' => [],
                ],
            ],
        ];
    }
}
