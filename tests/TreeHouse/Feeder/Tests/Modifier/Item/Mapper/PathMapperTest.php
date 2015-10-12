<?php

namespace TreeHouse\Feeder\Tests\Modifier\Item\Mapper;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Item\Mapper\PathMapper;

class PathMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMapper()
    {
        $mapping = [
            'foo' => 'bar',
        ];

        $mapper = new PathMapper($mapping);

        $this->assertEquals('bar', $mapper->mapToField('foo'));
        $this->assertEquals('foo', $mapper->mapFromField('bar'));

        $this->assertNull($mapper->mapToField('foo1'));
        $this->assertNull($mapper->mapFromField('bar1'));
    }

    public function testChangeMapping()
    {
        $mapping = [
            'foo' => 'bar1',
        ];

        $mapper = new PathMapper();
        $mapper->set($mapping);

        $this->assertEquals('bar1', $mapper->mapToField('foo'));
        $this->assertEquals('foo', $mapper->mapFromField('bar1'));

        $mapper->add('baz', 'qux');
        $this->assertEquals('qux', $mapper->mapToField('baz'));
    }

    public function testMapping()
    {
        $mapping = [
            'test-foo' => 'foo',
            'test-bar' => 'bar',
            'test-baz' => 'baz',
            'not-in-item' => 'should not appear in mapped',
        ];

        $item = [
            'test-foo' => 'foo value',
            'test-bar' => 'bar value',
            'test-baz' => null,
            'unmapped' => 'unmapped value',
        ];

        $expected = [
            'foo' => 'foo value',
            'bar' => 'bar value',
            'baz' => null,
            'unmapped' => 'unmapped value',
        ];

        $mapper = new PathMapper($mapping);
        $mapped = $mapper->map(new ParameterBag($item));

        $this->assertEquals($expected, $mapped->all());
    }

    public function testMappingWithOverride()
    {
        $mapping = [
            'test-foo' => 'foo',
            'other-foo' => 'foo',
            'test-bar' => 'bar',
        ];

        $item = [
            'test-foo' => 'foo value',
            'other-foo' => 'overriden foo value',
            'test-bar' => 'bar value',
        ];

        $expected = [
            'foo' => 'overriden foo value',
            'bar' => 'bar value',
        ];

        $mapper = new PathMapper($mapping);
        $mapped = $mapper->map(new ParameterBag($item));

        $this->assertEquals($expected, $mapped->all());
    }

    public function testMappingWithNoOverride()
    {
        $mapping = [
            'test-foo' => 'foo',
            'other-foo' => 'foo',
            'test-bar' => 'bar',
        ];

        $item = [
            'test-foo' => 'foo value',
            'other-foo' => '',
            'test-bar' => 'bar value',
        ];

        $expected = [
            'foo' => 'foo value',
            'bar' => 'bar value',
        ];

        $mapper = new PathMapper($mapping);
        $mapped = $mapper->map(new ParameterBag($item));

        $this->assertEquals($expected, $mapped->all());
    }

    public function testDeepMapping()
    {
        $mapping = [
            'foo' => 'foo',
            'test[bar]' => 'bar',
        ];

        $item = [
            'foo' => 'foo value',
            'test' => [
                'bar' => 'bar value',
            ],
        ];

        $expected = [
            'foo' => 'foo value',
            'bar' => 'bar value',
            'test' => [
                'bar' => 'bar value',
            ],
        ];

        $mapper = new PathMapper($mapping, true);
        $mapped = $mapper->map(new ParameterBag($item));

        $this->assertEquals($expected, $mapped->all());
    }
}
