<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\TransformationFailedException;
use TreeHouse\Feeder\Modifier\Data\Transformer\TransformerInterface as InnerTransformer;

class DataTransformer implements TransformerInterface
{
    /**
     * @var InnerTransformer
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $field;

    /**
     * @param InnerTransformer $transformer
     * @param string           $field
     */
    public function __construct(InnerTransformer $transformer, $field)
    {
        $this->transformer = $transformer;
        $this->field       = $field;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return TransformerInterface
     */
    public function getInnerTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param ParameterBag $item
     *
     * @throws TransformationFailedException
     */
    public function transform(ParameterBag $item)
    {
        if (!$item->has($this->field)) {
            return;
        }

        $value = $item->get($this->field);

        try {
            $newValue = $this->transformer->transform($value);
            $item->set($this->field, $newValue);
        } catch (TransformationFailedException $e) {
            // set the value to null as we couldn't transform it
            $item->set($this->field, null);

            throw new TransformationFailedException(
                sprintf(
                    'Transforming "%s" using "%s" failed with message: %s.',
                    $this->field,
                    get_class($this->transformer),
                    $e->getMessage()
                ),
                null,
                $e
            );
        }
    }
}
