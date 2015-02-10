<?php

namespace TreeHouse\Feeder;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Event\FailedItemModificationEvent;
use TreeHouse\Feeder\Event\InvalidItemEvent;
use TreeHouse\Feeder\Event\ItemModificationEvent;
use TreeHouse\Feeder\Event\ItemNotModifiedEvent;
use TreeHouse\Feeder\Exception\FilterException;
use TreeHouse\Feeder\Exception\ModificationException;
use TreeHouse\Feeder\Exception\ValidationException;
use TreeHouse\Feeder\Modifier\Item\Filter\FilterInterface;
use TreeHouse\Feeder\Modifier\Item\Mapper\MapperInterface;
use TreeHouse\Feeder\Modifier\Item\ModifierInterface;
use TreeHouse\Feeder\Modifier\Item\Transformer\TransformerInterface;
use TreeHouse\Feeder\Modifier\Item\Validator\ValidatorInterface;
use TreeHouse\Feeder\Reader\ReaderInterface;

class Feed
{
    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ModifierInterface[]
     */
    protected $modifiers = [];

    /**
     * @var array
     */
    protected $continues = [];

    /**
     * @param ReaderInterface          $reader
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ReaderInterface $reader, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->reader = $reader;
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
    }

    /**
     * @return ReaderInterface
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return ModifierInterface[]
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * @param FilterInterface $filter
     * @param integer|null    $position
     */
    public function addFilter(FilterInterface $filter, $position = null)
    {
        $this->addModifier($filter, $position);
    }

    /**
     * @param MapperInterface $mapper
     * @param integer|null    $position
     */
    public function addMapper(MapperInterface $mapper, $position = null)
    {
        $this->addModifier($mapper, $position);
    }

    /**
     * @param TransformerInterface $transformer
     * @param integer|null         $position
     */
    public function addTransformer(TransformerInterface $transformer, $position = null)
    {
        $this->addModifier($transformer, $position);
    }

    /**
     * @param ValidatorInterface $validator
     * @param integer|null       $position
     */
    public function addValidator(ValidatorInterface $validator, $position = null)
    {
        $this->addModifier($validator, $position);
    }

    /**
     * @param ModifierInterface $modifier
     * @param integer           $position
     * @param boolean           $continueOnException
     *
     * @throws \InvalidArgumentException
     */
    public function addModifier(ModifierInterface $modifier, $position = null, $continueOnException = false)
    {
        if (null === $position) {
            $position = sizeof($this->modifiers) ? (max(array_keys($this->modifiers)) + 1) : 0;
        }

        if (!is_numeric($position)) {
            throw new \InvalidArgumentException('Position must be a number');
        }

        if (array_key_exists($position, $this->modifiers)) {
            throw new \InvalidArgumentException(sprintf('There already is a modifier at position %d', $position));
        }

        $this->modifiers[$position] = $modifier;
        $this->continues[$position] = $continueOnException;

        ksort($this->modifiers);
    }

    /**
     * @param ModifierInterface $modifier
     */
    public function removeModifier(ModifierInterface $modifier)
    {
        foreach ($this->modifiers as $position => $_modifier) {
            if ($_modifier === $modifier) {
                unset($this->modifiers[$position]);

                break;
            }
        }
    }

    /**
     * @param integer $position
     *
     * @throws \OutOfBoundsException
     */
    public function removeModifierAt($position)
    {
        if (!array_key_exists($position, $this->modifiers)) {
            throw new \OutOfBoundsException(sprintf('There is no modifier at position %d', $position));
        }

        unset($this->modifiers[$position]);
    }

    /**
     * @param integer $position
     *
     * @return boolean
     */
    public function hasModifierAt($position)
    {
        return array_key_exists($position, $this->modifiers);
    }

    /**
     * @return ParameterBag|null
     */
    public function getNextItem()
    {
        while ($item = $this->reader->read()) {
            try {
                $this->eventDispatcher->dispatch(FeedEvents::ITEM_PRE_MODIFICATION, new ItemModificationEvent($item));
                $item = $this->modify($item);
                $this->eventDispatcher->dispatch(FeedEvents::ITEM_POST_MODIFICATION, new ItemModificationEvent($item));

                return $item;
            } catch (FilterException $e) {
                $this->eventDispatcher->dispatch(
                    FeedEvents::ITEM_FILTERED,
                    new ItemNotModifiedEvent($item, $e->getMessage())
                );
            } catch (ValidationException $e) {
                $this->eventDispatcher->dispatch(
                    FeedEvents::ITEM_INVALID,
                    new InvalidItemEvent($item, $e->getMessage())
                );
            } catch (ModificationException $e) {
                if ($e->getPrevious()) {
                    $e = $e->getPrevious();
                }

                $this->eventDispatcher->dispatch(
                    FeedEvents::ITEM_FAILED,
                    new ItemNotModifiedEvent($item, $e->getMessage())
                );
            }
        }

        return null;
    }

    /**
     * @param ParameterBag $item
     *
     * @throws FilterException
     * @throws ModificationException
     * @throws ValidationException
     *
     * @return ParameterBag
     */
    protected function modify(ParameterBag &$item)
    {
        foreach ($this->modifiers as $position => $modifier) {
            try {
                if ($modifier instanceof FilterInterface) {
                    $modifier->filter($item);
                }

                if ($modifier instanceof MapperInterface) {
                    $item = $modifier->map($item);
                }

                if ($modifier instanceof TransformerInterface) {
                    $modifier->transform($item);
                }

                if ($modifier instanceof ValidatorInterface) {
                    $modifier->validate($item);
                }
            } catch (FilterException $e) {
                // filter exceptions don't get to continue
                throw $e;
            } catch (ValidationException $e) {
                // validation exceptions don't get to continue
                throw $e;
            } catch (ModificationException $e) {
                // notify listeners of this failure, give them the option to stop propagation
                $event = new FailedItemModificationEvent($item, $modifier, $e);
                $event->setContinue($this->continues[$position]);

                $this->eventDispatcher->dispatch(FeedEvents::ITEM_MODIFICATION_FAILED, $event);

                if (!$event->getContinue()) {
                    throw $e;
                }
            }
        }

        return $item;
    }
}
