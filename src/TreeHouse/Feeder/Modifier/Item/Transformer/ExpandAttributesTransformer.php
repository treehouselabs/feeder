<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Exception\UnexpectedTypeException;

class ExpandAttributesTransformer implements TransformerInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var boolean
     */
    protected $removeOriginal;

    /**
     * @var array
     */
    protected $overwriteExisting;

    /**
     * @param string  $field             Expand attributes in this field, if omitted, all root-level attributes are
     *                                   expanded
     * @param boolean $removeOriginal    Whether to remove the original attribute
     * @param array   $overwriteExisting Keys that may be overwritten when they already exist
     *
     * @throws UnexpectedTypeException
     */
    public function __construct($field = null, $removeOriginal = false, array $overwriteExisting = [])
    {
        if (!is_string($field) && !is_null($field)) {
            throw new UnexpectedTypeException($field, 'string or null');
        }

        $this->field             = $field;
        $this->removeOriginal    = $removeOriginal;
        $this->overwriteExisting = $overwriteExisting;
    }

    /**
     * @inheritdoc
     */
    public function transform(ParameterBag $item)
    {
        if (!$this->field) {
            // expand root-level attributes
            $item->replace($this->expand($item->all()));

            return;
        }

        // proceed only when the field exists and is an array
        $value = $item->get($this->field);
        if (!is_array($value)) {
            return;
        }

        $item->set($this->field, $this->expand($value));
    }

    /**
     * @param array $value
     *
     * @return array
     */
    protected function expand(array $value)
    {
        foreach ($value as $name => $val) {
            // attributes are converted to @attribute
            if (substr($name, 0, 1) === '@') {
                if ($this->removeOriginal) {
                    unset($value[$name]);
                }

                // the new name
                $name = ltrim($name, '@');

                // if key already exists, check if we may overwrite it
                if (array_key_exists($name, $value) && !in_array($name, $this->overwriteExisting)) {
                    continue;
                }

                $value[$name] = $val;
            }
        }

        return $value;
    }
}
