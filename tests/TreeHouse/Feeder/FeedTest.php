<?php

namespace TreeHouse\Feeder;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Event\FailedItemModificationEvent;
use TreeHouse\Feeder\Exception\FilterException;
use TreeHouse\Feeder\Exception\ModificationException;
use TreeHouse\Feeder\Exception\ValidationException;
use TreeHouse\Feeder\Modifier\Item\Filter\CallbackFilter;
use TreeHouse\Feeder\Modifier\Item\Mapper\PathMapper;
use TreeHouse\Feeder\Modifier\Item\ModifierInterface;
use TreeHouse\Feeder\Modifier\Item\Transformer\CallbackTransformer;
use TreeHouse\Feeder\Modifier\Item\Validator\CallbackValidator;
use TreeHouse\Feeder\Reader\ReaderInterface;
use TreeHouse\Feeder\Reader\XmlReader;
use TreeHouse\Feeder\Resource\StringResource;
use TreeHouse\Feeder\Tests\Mock\EventDispatcherMock;

class FeedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var ModifierInterface
     */
    protected $modifier;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->modifier = new CallbackTransformer(function () {});
        $this->reader   = new XmlReader(new StringResource('<foo><bar>Baz</bar></foo>'));
        $this->reader->setNodeCallback('foo');
    }

    public function testConstructor()
    {
        $feed = new Feed($this->reader);

        $this->assertInstanceOf(Feed::class, $feed);
        $this->assertSame($this->reader, $feed->getReader());
        $this->assertInstanceOf(EventDispatcherInterface::class, $feed->getEventDispatcher());
    }

    public function testAddModifier()
    {
        $feed = new Feed($this->reader);
        $feed->addModifier($this->modifier);
        $feed->addModifier($this->modifier);

        $this->assertEquals([0 => $this->modifier, 1 => $this->modifier], $feed->getModifiers());
    }

    public function testAddModifierAt()
    {
        $feed = new Feed($this->reader);
        $feed->addModifier($this->modifier, 2);

        $this->assertEquals([2 => $this->modifier], $feed->getModifiers());
    }

    public function testAddModifierSort()
    {
        $feed = new Feed($this->reader);
        $feed->addModifier($this->modifier, 2);
        $feed->addModifier($this->modifier, 1);

        $this->assertEquals([1 => $this->modifier, 2 => $this->modifier], $feed->getModifiers());
    }

    public function testHasModifierAt()
    {
        $feed = new Feed($this->reader);

        $this->assertFalse($feed->hasModifierAt(1));

        $feed->addModifier($this->modifier, 1);

        $this->assertTrue($feed->hasModifierAt(1));
    }

    public function testRemoveModifier()
    {
        $feed = new Feed($this->reader);
        $feed->addModifier($this->modifier);

        $this->assertCount(1, $feed->getModifiers());

        $feed->removeModifier($this->modifier);

        $this->assertEmpty($feed->getModifiers());
    }

    public function testRemoveModifierAt()
    {
        $feed = new Feed($this->reader);
        $feed->addModifier($this->modifier, 1);
        $feed->addModifier($this->modifier, 2);
        $feed->addModifier($this->modifier, 3);

        $this->assertTrue($feed->hasModifierAt(1));
        $this->assertTrue($feed->hasModifierAt(2));
        $this->assertTrue($feed->hasModifierAt(3));

        $feed->removeModifierAt(2);

        $this->assertTrue($feed->hasModifierAt(1));
        $this->assertFalse($feed->hasModifierAt(2));
        $this->assertTrue($feed->hasModifierAt(3));
    }

    public function testAddMapper()
    {
        $mapper = new PathMapper();

        $feed = new Feed($this->reader);
        $feed->addMapper($mapper);

        $this->assertEquals([$mapper], $feed->getModifiers());
    }

    public function testAddTransformer()
    {
        $transformer = new CallbackTransformer(function () {});

        $feed = new Feed($this->reader);
        $feed->addTransformer($transformer);

        $this->assertEquals([$transformer], $feed->getModifiers());
    }

    public function testAddFilter()
    {
        $filter = new CallbackFilter(function () {});

        $feed = new Feed($this->reader);
        $feed->addFilter($filter);

        $this->assertEquals([$filter], $feed->getModifiers());
    }

    public function testAddValidator()
    {
        $validator = new CallbackValidator(function () {});

        $feed = new Feed($this->reader);
        $feed->addValidator($validator);

        $this->assertEquals([$validator], $feed->getModifiers());
    }

    public function testIterate()
    {
        $feed = new Feed($this->reader);
        $item = $feed->getNextItem();

        $this->assertInstanceOf(ParameterBag::class, $item);
        $this->assertTrue($item->has('bar'));
        $this->assertEquals('Baz', $item->get('bar'));

        $this->assertNull($feed->getNextItem());
    }

    public function testModify()
    {
        $feed = new Feed($this->reader);
        $feed->addModifier(new CallbackTransformer(function (ParameterBag $item) {
            $item->set('foo', 'bar');
        }));

        $item = $feed->getNextItem();

        $this->assertEquals('Baz', $item->get('bar'));
        $this->assertTrue($item->has('foo'));
        $this->assertEquals('bar', $item->get('foo'), 'Item should have been modified');
    }

    public function testModificationOrder()
    {
        $feed = new Feed($this->reader);
        $feed->addModifier(
            new CallbackTransformer(function (ParameterBag $item) {
                $item->set('bar', $item->get('bar') . 'z');
            }),
            2
        );

        $feed->addModifier(
            new CallbackTransformer(function (ParameterBag $item) {
                $item->set('bar', $item->get('bar') . 'Z');
            }),
            1
        );

        $item = $feed->getNextItem();

        $this->assertEquals('BazZz', $item->get('bar'), 'The order of modifiers must be preserved');
    }

    public function testFilter()
    {
        $dispatcher = new EventDispatcherMock();
        $feed = new Feed($this->reader, $dispatcher);
        $feed->addModifier(new CallbackFilter(function () {
            throw new FilterException();
        }));

        $this->assertNull($feed->getNextItem(), 'When item is filtered, it is not returned');

        $events = $dispatcher->getDispatchedEvents();
        $this->assertArrayHasKey(FeedEvents::ITEM_FILTERED, $events);
        $this->assertNotEmpty($events[FeedEvents::ITEM_FILTERED]);
    }

    public function testValidator()
    {
        $dispatcher = new EventDispatcherMock();
        $feed = new Feed($this->reader, $dispatcher);
        $feed->addModifier(new CallbackFilter(function () {
            throw new ValidationException();
        }));

        $this->assertNull($feed->getNextItem(), 'When validation fails, the item is not returned');

        $events = $dispatcher->getDispatchedEvents();
        $this->assertArrayHasKey(FeedEvents::ITEM_INVALID, $events);
        $this->assertNotEmpty($events[FeedEvents::ITEM_INVALID]);
    }

    public function testModificationException()
    {
        $dispatcher = new EventDispatcherMock();
        $feed = new Feed($this->reader, $dispatcher);
        $feed->addModifier(new CallbackTransformer(function () {
            throw new ModificationException();
        }));

        $this->assertNull($feed->getNextItem(), 'When modification fails, the item is not returned');

        $events = $dispatcher->getDispatchedEvents();
        $this->assertArrayHasKey(FeedEvents::ITEM_MODIFICATION_FAILED, $events);
        $this->assertNotEmpty($events[FeedEvents::ITEM_MODIFICATION_FAILED]);
        $this->assertArrayHasKey(FeedEvents::ITEM_FAILED, $events);
        $this->assertNotEmpty($events[FeedEvents::ITEM_FAILED]);
    }

    public function testContinueOnModificationException()
    {
        $feed = new Feed($this->reader);
        $feed->addModifier(
            new CallbackTransformer(function () {
                throw new ModificationException();
            }),
            1,
            true
        );

        $item = $feed->getNextItem();

        $this->assertInstanceOf(ParameterBag::class, $item, 'Modification should continue even though a modifier failed');
    }

    public function testChangeContinueOnModificationException()
    {
        $feed = new Feed($this->reader);
        $feed->getEventDispatcher()->addListener(FeedEvents::ITEM_MODIFICATION_FAILED, function (FailedItemModificationEvent $e) {
            $e->setContinue(false);
        });

        $feed->addModifier(
            new CallbackTransformer(function () {
                throw new ModificationException();
            }),
            1,
            true
        );

        $this->assertNull($feed->getNextItem(), 'The modification should have not continued');
    }
}
