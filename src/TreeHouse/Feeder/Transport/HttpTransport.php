<?php

namespace TreeHouse\Feeder\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ProgressEvent;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use TreeHouse\Feeder\Event\FetchProgressEvent;
use TreeHouse\Feeder\Exception\TransportException;
use TreeHouse\Feeder\FeedEvents;

class HttpTransport extends AbstractTransport implements SubscriberInterface, ProgressAwareInterface
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var integer
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
        $client = new Client();
        $client->setDefaultOption('headers/User-Agent', static::getDefaultUserAgent());

        $conn = new Connection([
            'url'  => $url,
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
        $this->client->getEmitter()->attach($this);
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
     * @return integer|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function getEvents()
    {
        return [
            'complete' => ['onComplete', 'first'],
            'progress' => ['onProgress'],
        ];
    }

    /**
     * @param ProgressEvent $event
     */
    public function onProgress(ProgressEvent $event)
    {
        $this->size = $event->downloadSize;

        $progressEvent = new FetchProgressEvent($event->downloaded, $event->downloadSize);
        $this->eventDispatcher->dispatch(FeedEvents::FETCH_PROGRESS, $progressEvent);
    }

    /**
     * @param CompleteEvent $event
     */
    public function onComplete(CompleteEvent $event)
    {
        if (($response = $event->getResponse()) && ($lastModified = $response->getHeader('Last-Modified'))) {
            $this->lastModified = new \DateTime($lastModified);
        }
    }

    /**
     * @throws \LogicException
     *
     * @return RequestInterface
     */
    protected function createRequest()
    {
        if (!$this->client) {
            throw new \LogicException('No client set to use for downloading');
        }

        $options = [];
        if (($user = $this->getUser()) && ($pass = $this->getPass())) {
            $options['auth'] = [$user, $pass];
        }

        return $this->client->createRequest('GET', $this->getUrl(), $options);
    }

    /**
     * @inheritdoc
     */
    protected function doFetch($destination)
    {
        $request = $this->createRequest();

        try {
            $response = $this->client->send($request);
        } catch (RequestException $e) {
            throw new TransportException(sprintf('Could not download feed: %s', $e->getMessage()), null, $e);
        }

        if (!$body = $response->getBody()) {
            throw new TransportException('Server did not return any content, status code was ' . $response->getStatusCode());
        }

        $f = fopen($destination, 'w');
        while (!$body->eof()) {
            fwrite($f, $body->read(1024));
        }
        fclose($f);
    }

    /**
     * @inheritdoc
     */
    protected function isFresh($destination, \DateTime $maxAge = null)
    {
        return false;
    }
}
