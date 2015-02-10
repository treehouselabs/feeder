<?php

namespace TreeHouse\Feeder\Writer;

use XmlWriter as BaseXmlWriter;

class XmlWriter implements WriterInterface
{
    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * @var BaseXmlWriter
     */
    protected $writer;

    /**
     * @var boolean
     */
    protected $indent = false;

    /**
     * @var string
     */
    protected $rootNode = 'feed';

    /**
     * @inheritdoc
     */
    public function __construct(\SplFileObject $file = null)
    {
        $this->file = $file;
    }

    /**
     * The clone magic method
     */
    public function __clone()
    {
        $this->file   = null;
        $this->writer = null;
    }

    /**
     * @inheritdoc
     */
    public function setFile(\SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * @param boolean $ident
     */
    public function setIndent($ident)
    {
        $this->indent = (boolean) $ident;
    }

    /**
     * @param string $node
     */
    public function setRootNode($node)
    {
        $this->rootNode = $node;
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        if (!$this->file) {
            throw new \LogicException('Set a file first');
        }

        if ($this->writer) {
            throw new \LogicException('Writer already started');
        }

        $this->writer = new BaseXmlWriter();
        $this->writer->openUri($this->file->getPathname());
        $this->writer->setIndent($this->indent);
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->write(sprintf('<%s>', $this->rootNode));
    }

    /**
     * @inheritdoc
     */
    public function write($data)
    {
        if (!$this->writer) {
            throw new \LogicException('Start writer first');
        }

        $this->writer->writeRaw($data);
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        if (!$this->writer) {
            throw new \LogicException('Start writer first');
        }

        $this->writer->flush();
    }

    /**
     * @inheritdoc
     */
    public function end()
    {
        if (!$this->writer) {
            throw new \LogicException('Start writer first');
        }

        $this->write(sprintf('</%s>', $this->rootNode));
        $this->writer->endDocument();
        $this->flush();

        $this->writer = null;
    }
}
