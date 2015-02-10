<?php

namespace TreeHouse\Feeder\Resource;

use TreeHouse\Feeder\Exception\TransportException;

class StringResource implements ResourceInterface
{
    /**
     * @var string
     */
    protected $data;

    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * @param string $data
     *
     * @throws \InvalidArgumentException When anything other than a string is passed
     */
    public function __construct($data)
    {
        if (!is_string($data)) {
            throw new \InvalidArgumentException('You must pass a string to a StringResource');
        }

        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function getTransport()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function setFile(\SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function getFile()
    {
        if ($this->file === null) {
            try {
                $this->file = new TempFile();
                $this->file->fwrite($this->data);
                $this->file->fseek(0);
            } catch (\RuntimeException $e) {
                throw new TransportException($e->getMessage(), null, $e);
            }
        }

        return $this->file;
    }
}
