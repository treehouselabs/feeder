<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Modifier\Data\Transformer\TransformerInterface as DataTransformerInterface;

class RecursiveTransformer implements TransformerInterface
{
    /**
     * Transformer that will be applied recursively.
     *
     * @var DataTransformerInterface
     */
    protected $transformer;

    /**
     * Constructor.
     *
     * @param DataTransformerInterface $transformer transformer to apply recursively
     */
    public function __construct(DataTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param ParameterBag $item
     *
     * @return mixed
     */
    public function transform(ParameterBag $item)
    {
        foreach ($item->all() as $key => $value) {
            $item->set($key, $this->transformRecursive($value));
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function transformRecursive($value)
    {
        if (is_array($value) || $value instanceof \Traversable) {
            foreach ($value as &$subvalue) {
                $subvalue = $this->transformRecursive($subvalue);
            }
        } else {
            $value = $this->transformer->transform($value);
        }

        return $value;
    }
}
