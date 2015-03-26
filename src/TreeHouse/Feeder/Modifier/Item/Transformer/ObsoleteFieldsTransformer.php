<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Makes sure obsolete fields are removed
 */
class ObsoleteFieldsTransformer implements TransformerInterface
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @param array $fields The fields to keep. Any other field will be removed.
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
            if (!in_array($key, $this->fields)) {
                $item->remove($key);
            }
        }
    }
}
