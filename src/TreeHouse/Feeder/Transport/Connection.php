<?php

namespace TreeHouse\Feeder\Transport;

class Connection implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->options[] = $value;
        } else {
            $this->options[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->options[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->options[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return isset($this->options[$offset]) ? $this->options[$offset] : null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return md5(json_encode($this->options));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        foreach (['name', 'url', 'file'] as $attempt) {
            if (array_key_exists($attempt, $this->options)) {
                return $this->options[$attempt];
            }
        }

        return json_encode($this);
    }
}
