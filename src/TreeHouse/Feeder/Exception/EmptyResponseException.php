<?php
declare(strict_types = 1);

namespace TreeHouse\Feeder\Exception;

use Psr\Http\Message\ResponseInterface;

class EmptyResponseException extends TransportException
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        parent::__construct('Server did not return any content, status code was ' . $response->getStatusCode());
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
