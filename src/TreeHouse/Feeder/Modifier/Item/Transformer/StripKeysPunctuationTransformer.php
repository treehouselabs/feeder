<?php

namespace TreeHouse\Feeder\Modifier\Item\Transformer;

use Symfony\Component\HttpFoundation\ParameterBag;

class StripKeysPunctuationTransformer implements TransformerInterface
{
    /**
     * @var array
     */
    protected $punctuation;

    /**
     * @var string
     */
    protected $regex;

    /**
     * @param array $punctuation
     */
    public function __construct(array $punctuation = ['.', ',', ':', ';'])
    {
        $this->punctuation = $punctuation;

        $this->regex = sprintf(
            '/[%s+]/',
            implode(
                '',
                array_map(
                    function ($value) {
                        return preg_quote($value, '/');
                    },
                    $this->punctuation
                )
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function transform(ParameterBag $item)
    {
        $parameters = $item->all();
        $this->stripKeysPunctuation($parameters);
        $item->replace($parameters);
    }

    /**
     * @param array $arr
     */
    protected function stripKeysPunctuation(array &$arr)
    {
        $new = [];

        foreach ($arr as $key => &$value) {
            if (is_array($value)) {
                $this->stripKeysPunctuation($value);
            }

            $new[$this->strip($key)] = $value;
        }

        $arr = $new;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function strip($string)
    {
        return preg_replace($this->regex, '', $string);
    }
}
