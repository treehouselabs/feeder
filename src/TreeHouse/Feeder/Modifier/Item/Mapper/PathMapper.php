<?php

namespace TreeHouse\Feeder\Modifier\Item\Mapper;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Performs deep path search in ParameterBag.
 * This supports fields like: foo[bar][baz]
 */
class PathMapper implements MapperInterface
{
    /**
     * @var array<string, string>
     */
    protected $mapping = [];

    /**
     * Whether to use deep path search. This supports fields like: foo[bar][baz]
     *
     * @var boolean
     */
    protected $deep = true;

    /**
     * @param array   $mapping The mapping
     * @param boolean $deep    Whether to use deep path search. This supports fields like: foo[bar][baz]
     */
    public function __construct(array $mapping = [], $deep = true)
    {
        $this->deep = $deep;
        $this->set($mapping);
    }

    /**
     * @inheritdoc
     */
    public function map(ParameterBag $item)
    {
        foreach ($this->mapping as $from => $to) {
            // use a special kind of null value to check, because we do want a
            // `null` value if it's actually set, but we cannot use the has()
            // method on deep paths, like foo[bar]
            if ('__null__' === $value = $item->get($from, '__null__', $this->deep)) {
                continue;
            }

            // if value is null, only set it when we don't already have this value
            if ($item->has($to) && !$this->mayOverride($item->get($to), $value)) {
                $item->remove($from);
                continue;
            }

            $item->set($to, $value);

            // remove the original if the key is mapped to a different key
            if ($to !== $from) {
                $item->remove($from);
            }
        }

        return $item;
    }

    /**
     * @param string $fromField
     * @param string $toField
     */
    public function add($fromField, $toField)
    {
        $this->mapping[$fromField] = $toField;
    }

    /**
     * @param array<string, string> $mapping
     */
    public function set(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @param string $fromField
     *
     * @return string|null
     */
    public function mapToField($fromField)
    {
        return array_key_exists($fromField, $this->mapping) ? $this->mapping[$fromField] : null;
    }

    /**
     * @param string $toField
     *
     * @return string|null
     */
    public function mapFromField($toField)
    {
        if (false !== $key = array_search($toField, $this->mapping)) {
            return $key;
        }

        return null;
    }

    /**
     * Decides whether a value may override a previous value
     *
     * @param mixed $previous
     * @param mixed $value
     *
     * @return boolean
     *
     * @todo implement override strategy with options: keep and override
     */
    protected function mayOverride($previous, $value)
    {
        return !empty($value) || empty($previous);
    }
}
