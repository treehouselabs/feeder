<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class UnderscoreKeysTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform(ParameterBag $item)
    {
        $parameters = $item->all();
        $this->underscoreKeys($parameters);
        $item->replace($parameters);
    }

    /**
     * @param array $arr
     */
    protected function underscoreKeys(array &$arr)
    {
        $new = [];

        foreach ($arr as $key => &$value) {
            if (is_array($value)) {
                $this->underscoreKeys($value);
            }

            $new[$this->underscore($key)] = $value;
        }

        $arr = $new;
    }

    /**
     * Copied from Doctrine's UnderscoreNamingStrategy
     *
     * @param  string $string
     * @return string
     */
    protected function underscore($string)
    {
        return strtolower(
            str_replace('-', '_', preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string))
        );
    }
}
