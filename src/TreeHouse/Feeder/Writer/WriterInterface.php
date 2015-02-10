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
     *
     * @return void
     */
    public function setFile(\SplFileObject $file);

    /**
     * @throws \RuntimeException If writer is already started
     *
     * @return void
     */
    public function start();

    /**
     * @param string $data
     *
     * @throws \RuntimeException If writer is not yet started
     *
     * @return void
     */
    public function write($data);

    /**
     * @throws \RuntimeException If writer is not yet started
     *
     * @return void
     */
    public function flush();

    /**
     * @throws \RuntimeException If writer is not yet started
     *
     * @return void
     */
    public function end();
}
