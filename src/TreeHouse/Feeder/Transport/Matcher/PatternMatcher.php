<?php

namespace TreeHouse\Feeder\Transport\Matcher;

class PatternMatcher implements MatcherInterface
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function match(array $files)
    {
        foreach ($files as $file) {
            if (preg_match($this->pattern, $file)) {
                return $file;
            }
        }
    }

    public function __toString()
    {
        return $this->pattern;
    }
}
