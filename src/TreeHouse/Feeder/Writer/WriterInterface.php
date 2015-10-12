<?php

namespace TreeHouse\Feeder\Writer;

interface WriterInterface
{
    /**
     * @param \SplFileObject $file
     */
    public function __construct(\SplFileObject $file);

    /**
     * @param \SplFileObject $file
     */
    public function setFile(\SplFileObject $file);

    /**
     * @throws \RuntimeException If writer is already started
     */
    public function start();

    /**
     * @param string $data
     *
     * @throws \RuntimeException If writer is not yet started
     */
    public function write($data);

    /**
     * @throws \RuntimeException If writer is not yet started
     */
    public function flush();

    /**
     * @throws \RuntimeException If writer is not yet started
     */
    public function end();
}
