<?php

namespace TreeHouse\Feeder\Tests\Mock;

use TreeHouse\Feeder\Transport\AbstractTransport;
use TreeHouse\Feeder\Transport\Connection;

class TransportMock extends AbstractTransport
{
    public function __construct()
    {
        parent::__construct(new Connection([]));
    }

    /**
     * @inheritdoc
     */
    protected function doFetch($destination)
    {
    }

    /**
     * @inheritdoc
     */
    public function getLastModifiedDate()
    {
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
    }
}
