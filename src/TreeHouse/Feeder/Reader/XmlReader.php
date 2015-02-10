<?php

namespace TreeHouse\Feeder\Reader;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Serializer;
use TreeHouse\Feeder\Exception\ReadException;
use TreeHouse\Feeder\Resource\ResourceInterface;

class XmlReader extends AbstractReader
{
    /**
     * @var \XMLReader
     */
    protected $reader;

    /**
     * @var callable
     */
    protected $nextNode;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var integer
     */
    protected $key;

    /**
     * @param mixed                    $resources  Optional resource collection. Can be a Resource, an array of
     *                                             Resource's, or a ResourceCollection. When empty, a new collection
     *                                             will be created.
     * @param EventDispatcherInterface $dispatcher Optional event dispatcher.
     */
    public function __construct($resources = null, EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct($resources, $dispatcher);

        $this->serializer = new Serializer([new CustomNormalizer()], ['xml' => new XmlEncoder()]);
    }

    /**
     * @param mixed $nextNode Callback to get the next node from the current resource. Can be a callback or a node name.
     *
     * @throws \InvalidArgumentException
     *
     * @return callable
     */
    public function setNodeCallback($nextNode)
    {
        if ($nextNode instanceof \Closure) {
            return $this->nextNode = $nextNode;
        }

        if (!is_string($nextNode)) {
            throw new \InvalidArgumentException('Expecting a string of callback for nextNode');
        }

        $nodeName = mb_strtolower($nextNode);

        return $this->nextNode = function (\XMLReader $reader) use ($nodeName) {
            while ($this->readerOperation($reader, 'read')) {
                // stop if we found our node
                if (($reader->nodeType === \XMLReader::ELEMENT) && (mb_strtolower($reader->name) === $nodeName)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * @inheritdoc
     */
    protected function doKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    protected function doCurrent()
    {
        return $this->readerOperation($this->reader, 'readOuterXml');
    }

    /**
     * @inheritdoc
     */
    protected function doNext()
    {
        $this->moveToNextNode($this->reader);
    }

    /**
     * @inheritdoc
     */
    protected function doRewind()
    {
        $this->reader->close();
        $this->open($this->resource->getFile()->getPathname());

        $this->key = -1;

        $this->next();
    }

    /**
     * @inheritdoc
     */
    protected function doValid()
    {
        return (boolean) $this->doCurrent();
    }

    /**
     * @param \XMLReader $reader
     *
     * @throws \LogicException
     *
     * @return mixed
     */
    protected function moveToNextNode(\XMLReader $reader)
    {
        if (!$this->nextNode instanceof \Closure) {
            throw new \LogicException('No callback set to get next node');
        }

        $this->key++;

        return call_user_func($this->nextNode, $reader);
    }

    /**
     * @inheritdoc
     */
    protected function createReader(ResourceInterface $resource)
    {
        $this->reader = new \XmlReader();
        $this->open($resource->getFile()->getPathname());

        $this->key = -1;
        $this->next();
    }

    /**
     * @inheritdoc
     */
    protected function serialize($data)
    {
        return new ParameterBag((array) $this->serializer->decode($data, 'xml'));
    }

    /**
     * @param string  $file
     * @param integer $options
     */
    protected function open($file, $options = null)
    {
        if (is_null($options)) {
            $options = LIBXML_NOENT | LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_NOERROR | LIBXML_NOWARNING;
        }

        $this->reader->open($file, null, $options);
    }

    /**
     * @return string
     */
    private function getXmlError()
    {
        // just return the first error
        if ($error = libxml_get_last_error()) {
            return sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING === $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }

        return null;
    }

    /**
     * @param \XmlReader $reader
     * @param string     $method
     * @param array      $args
     *
     * @throws ReadException
     *
     * @return mixed
     */
    private function readerOperation(\XmlReader $reader, $method, array $args = [])
    {
        // clear any previous errors
        libxml_clear_errors();

        // remember current settings
        $errors = libxml_use_internal_errors(true);
        $entities = libxml_disable_entity_loader(true);

        // perform the operation
        $retval = call_user_func_array([$reader, $method], $args);

        // get the last error, if any
        $error = $this->getXmlError();

        // reset everything, clear the error buffer again
        libxml_clear_errors();
        libxml_use_internal_errors($errors);
        libxml_disable_entity_loader($entities);

        if ($error) {
            throw new ReadException($error);
        }

        return $retval;
    }
}
