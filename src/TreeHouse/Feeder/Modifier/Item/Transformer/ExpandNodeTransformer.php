<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class ExpandNodeTransformer implements TransformerInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var bool
     */
    protected $removeOriginal;

    /**
     * @var array
     */
    protected $overwriteExisting;

    /**
     * @param string $field             Expand nodes in this field
     * @param bool   $removeOriginal    Whether to remove the original node
     * @param array  $overwriteExisting Keys that may be overwritten when they already exist
     */
    public function __construct($field, $removeOriginal = false, array $overwriteExisting = [])
    {
        $this->field = $field;
        $this->removeOriginal = $removeOriginal;
        $this->overwriteExisting = $overwriteExisting;
    }

    /**
     * @inheritdoc
     */
    public function transform(ParameterBag $item)
    {
        if ($item->has($this->field)) {
            $value = $item->get($this->field);

            // check if the field is an array
            if (is_array($value)) {
                $this->expand($value, $item);
            }

            // remove the compound field if requested
            if ($this->removeOriginal) {
                $item->remove($this->field);
            }
        }
    }

    /**
     * @param array        $value
     * @param ParameterBag $item
     */
    protected function expand(array $value, ParameterBag $item)
    {
        foreach ($value as $key => $val) {
            // if key already exists, check if we may overwrite it
            if ($item->has($key)) {
                if (!in_array($key, $this->overwriteExisting)) {
                    continue;
                }
            }

            $item->set($key, $val);
        }
    }
}
