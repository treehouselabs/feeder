<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class TrimTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform(ParameterBag $item)
    {
        $parameters = $item->all();
        $this->trimValues($parameters);
        $item->replace($parameters);
    }

    /**
     * @param array $arr
     */
    protected function trimValues(array &$arr)
    {
        foreach ($arr as &$value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            if (is_array($value)) {
                $this->trimValues($value);
            }
        }
    }
}
