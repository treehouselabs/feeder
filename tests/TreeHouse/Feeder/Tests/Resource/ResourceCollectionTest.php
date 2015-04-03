<?php

namespace TreeHouse\Feeder\Tests\Resource;

use TreeHouse\Feeder\Resource\ResourceCollection;
use TreeHouse\Feeder\Resource\ResourceInterface;
use TreeHouse\Feeder\Resource\StringResource;
use TreeHouse\Feeder\Resource\Transformer\CallbackTransformer;

class ResourceCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollection()
    {
        $collection = new ResourceCollection($this->getResources());

        $this->assertInstanceOf(ResourceCollection::class, $collection);
        $this->assertCount(3, $collection);

        // add 1 at the end
        $resource = new StringResource('test');
        $collection->enqueueAll([$resource]);
        $this->assertCount(4, $collection);
        $this->assertSame($resource, $collection->pop());

        // add 1 at the beginning
        $resource = new StringResource('test');
        $collection->unshiftAll([$resource]);
        $this->assertCount(4, $collection);
        $this->assertSame($resource, $collection->shift());
    }

    /**
     * @dataProvider getMethods
     */
    public function testTransform($method)
    {
        $resource = new StringResource('foo');
        $collection = new ResourceCollection([$resource]);
        $collection->addTransformer(
            new CallbackTransformer(function () {
                return new StringResource('bar');
            })
        );

        /** @var ResourceInterface $resource */
        $resource = ($method === 'offsetGet') ? $collection[0] : $collection->$method();

        $this->assertSame('bar', $resource->getFile()->fgets());
    }

    public function testTransformOnce()
    {
        $count = 0;

        $transformer = new CallbackTransformer(
            function () use ($count) {
                return new StringResource('bar' . ++$count);
            }
        );

        $resource = new StringResource('foo');
        $collection = new ResourceCollection([$resource]);
        $collection->addTransformer($transformer);

        $transformed = $collection->pop();
        $this->assertSame('bar1', $transformed->getFile()->fgets());

        // add the same resource again
        $collection->push($resource);
        $transformed = $collection->pop();
        $this->assertSame('foo', $transformed->getFile()->fgets(), 'A resource only gets transformed once');
    }

    public function getMethods()
    {
        return [
            ['current'],
            ['shift'],
            ['pop'],
            ['dequeue'],
            ['bottom'],
            ['top'],
            ['offsetGet'],
        ];
    }

    /**
     * @return ResourceInterface[]
     */
    private function getResources()
    {
        return [
            new StringResource('foo'),
            new StringResource('bar'),
            new StringResource('baz'),
        ];
    }
}
