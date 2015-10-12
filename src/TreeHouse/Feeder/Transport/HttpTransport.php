<?php

namespace TreeHouse\Feeder\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use TreeHouse\Feeder\Event\FetchProgressEvent;
use TreeHouse\Feeder\Exception\TransportException;
use TreeHouse\Feeder\FeedEvents;

class HttpTransport extends AbstractTransport implements ProgressAwareInterface
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var \DateTime
     */
    protected $lastModified;

    /**
     * @param string      $url
     * @param string|null $user
     * @param string|null $pass
     *
     * @return HttpTransport
     */
    public static function create($url, $user = null, $pass = null)
    {
        $client = new Client([
            'headers' => [
                'User-Agent' => static::getDefaultUserAgent(),
            ],
        ]);

        $conn = new Connection([
            'url' => $url,
            'user' => $user,
            'pass' => $pass,
        ]);

        $transport = new static($conn);
        $transport->setClient($client);

        return $transport;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getUrl();
    }

    /**
     * @throws \LogicException When url is not defined
     *
     * @return string
     */
    public function getUrl()
    {
        if (!isset($this->connection['url'])) {
            throw new \LogicException('No url defined');
        }

        return $this->connection['url'];
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->connection['url'] = $url;
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return isset($this->connection['user']) ? $this->connection['user'] : null;
    }

    /**
     * @return string|null
     */
    public function getPass()
    {
        return isset($this->connection['pass']) ? $this->connection['pass'] : null;
    }

    /**
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @inheritdoc
     */
    public function getLastModifiedDate()
    {
        return $this->lastModified;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param ResponseInterface $response
     */
    public function onHeaders(ResponseInterface $response)
    {
        // set the modified date if we got it in the response
        if (!empty($lastModified = $response->getHeader('Last-Modified'))) {
            $this->lastModified = new \DateTime(reset($lastModified));
        }
    }

    /**
     * @param int $downloadSize
     * @param int $downloaded
     */
    public function onProgress($downloadSize, $downloaded)
    {
        $this->size = $downloadSize;

        $progressEvent = new FetchProgressEvent($downloaded, $downloadSize);
        $this->eventDispatcher->dispatch(FeedEvents::FETCH_PROGRESS, $progressEvent);
    }

    /**
     * @inheritdoc
     */
    protected function doFetch($destination)
    {
        if (!$this->client) {
            throw new \LogicException('No client set to use for downloading');
        }

        $options = [
            RequestOptions::SINK => $destination,
            RequestOptions::ON_HEADERS => [$this, 'onHeaders'],
            RequestOptions::PROGRESS => [$this, 'onProgress'],
        ];

        if (($user = $this->getUser()) && ($pass = $this->getPass())) {
            $options['auth'] = [$user, $pass];
        }

        try {
            $response = $this->client->request('GET', $this->getUrl(), $options);

            if ($response->getBody()->getSize() === 0) {
                throw new TransportException('Server did not return any content, status code was ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            throw new TransportException(sprintf('Could not download feed: %s', $e->getMessage()), null, $e);
        }
    }

    /**
     * @inheritdoc
     */
    protected function isFresh($destination, \DateTime $maxAge = null)
    {
        return false;
    }
}
