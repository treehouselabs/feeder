<?php

namespace TreeHouse\Feeder\Transport\Matcher;

class FileMatcher implements MatcherInterface
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    public function match(array $files)
    {
        if (in_array($this->file, $files)) {
            return $this->file;
        }
    }

    public function __toString()
    {
        return $this->file;
    }
}
