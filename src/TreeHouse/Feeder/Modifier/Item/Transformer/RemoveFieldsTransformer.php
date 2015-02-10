<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Filters out unwanted fields.
 */
class RemoveFieldsTransformer implements TransformerInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @param array $fields The fields to remove
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @inheritdoc
     */
    public function transform(ParameterBag $item)
    {
        foreach ($item->keys() as $key) {
            if (in_array($key, $this->fields)) {
                $item->remove($key);
            }
        }
    }
}
