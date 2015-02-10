<?php

namespace TreeHouse\Feeder\Resource;

/**
 * Provides a temporary file to work with.
 */
final class TempFile extends \SplFileObject
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @param string|null $filename Leave empty to generate a tmp name
     */
    public function __construct($filename = null)
    {
        $this->fileName = $filename ?: tempnam(sys_get_temp_dir(), 'feeder');

        parent::__construct($this->fileName, 'a+');
    }

    /**
     * Clean up when done
     */
    public function __destruct()
    {
        if (file_exists($this->fileName)) {
            unlink($this->fileName);
        }
    }
}
