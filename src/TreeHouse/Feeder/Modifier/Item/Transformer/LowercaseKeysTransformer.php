<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class LowercaseKeysTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform(ParameterBag $item)
    {
        $parameters = $item->all();
        $this->lowercaseKeys($parameters);
        $item->replace($parameters);
    }

    /**
     * @param array $arr
     */
    protected function lowercaseKeys(array &$arr)
    {
        $arr = array_change_key_case($arr, CASE_LOWER);

        foreach ($arr as &$value) {
            if (is_array($value)) {
                $this->lowercaseKeys($value);
            }
        }
    }
}
