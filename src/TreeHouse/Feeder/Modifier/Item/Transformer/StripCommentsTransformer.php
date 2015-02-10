<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class StripCommentsTransformer implements TransformerInterface
{
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
        if (is_array($value)) {
            if (array_key_exists('#comment', $value)) {
                unset($value['#comment']);
            }

            foreach ($value as &$subvalue) {
                $subvalue = $this->transformRecursive($subvalue);
            }
        }

        return $value;
    }
}
