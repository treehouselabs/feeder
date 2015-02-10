<?php

namespace TreeHouse\Feeder\Transport\Matcher;

class GlobMatcher extends PatternMatcher
{
    /**
     * @var string
     */
    protected $globPattern;

    /**
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        $this->globPattern = $pattern;
        $parts = array_map('preg_quote', preg_split('/\*/', $pattern));

        parent::__construct('#^'.implode('[^\/]+', $parts).'$#i');
    }

    public function __toString()
    {
        return $this->globPattern;
    }
}
