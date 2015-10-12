<?php

namespace TreeHouse\Feeder\Reader;

use SplFileObject;
use Symfony\Component\HttpFoundation\ParameterBag;
use TreeHouse\Feeder\Resource\ResourceInterface;

class CsvReader extends AbstractReader
{
    /**
     * @var SplFileObject
     */
    protected $fileObject;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var bool
     */
    protected $useFirstRow;

    /**
     * @var string
     */
    protected $delimiter = ',';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var string
     */
    protected $escape = '\\';

    /**
     * @var bool
     */
    protected $convertNull;

    /**
     * Sets a mapping to use for each row. If the CSV has the column names exported,
     * you can use {@link useFirstRow} to auto-generate the field mapping.
     *
     * @param array $mapping
     */
    public function setFieldMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * When true, the first row in the CSV file is used to generate the field mapping.
     *
     * @param bool $bool
     */
    public function useFirstRow($bool = true)
    {
        $this->useFirstRow = (boolean) $bool;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter = ',')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param string $enclosure
     */
    public function setEnclosure($enclosure = '"')
    {
        $this->enclosure = $enclosure;
    }

    /**
     * @param string $escape
     */
    public function setEscape($escape = '\\')
    {
        $this->escape = $escape;
    }

    /**
     * @return int
     */
    public function getRowNumber()
    {
        return $this->key() + 1;
    }

    /**
     * When true, "null" and "NULL" are converted to NULL.
     *
     * @param bool $bool
     */
    public function convertNull($bool = true)
    {
        $this->convertNull = (boolean) $bool;
    }

    /**
     * @inheritdoc
     */
    protected function serialize($data)
    {
        // convert data keys if a mapping is given
        if ($this->mapping === null) {
            $this->mapping = array_combine(array_keys($data), array_keys($data));
        }

        $item = new ParameterBag();
        foreach ($this->mapping as $index => $field) {
            $value = array_key_exists($index, $data) ? $data[$index] : null;

            if ($this->convertNull && in_array($value, ['null', 'NULL'])) {
                $value = null;
            }

            $item->set($field, $value);
        }

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function doKey()
    {
        return $this->fileObject->key();
    }

    /**
     * @inheritdoc
     */
    protected function doCurrent()
    {
        return $this->fileObject->current();
    }

    /**
     * @inheritdoc
     */
    protected function doNext()
    {
        $this->fileObject->next();
    }

    /**
     * @inheritdoc
     */
    protected function doValid()
    {
        return $this->fileObject->valid() && is_array($this->fileObject->current());
    }

    /**
     * @inheritdoc
     */
    protected function doRewind()
    {
        $this->fileObject->rewind();
    }

    /**
     * @inheritdoc
     */
    protected function createReader(ResourceInterface $resource)
    {
        $this->fileObject = new SplFileObject($resource->getFile()->getPathname());
        $this->fileObject->setFlags(SplFileObject::READ_CSV | SplFileObject::DROP_NEW_LINE | SplFileObject::SKIP_EMPTY);
        $this->fileObject->setCsvControl($this->delimiter, $this->enclosure, $this->escape);

        if ($this->useFirstRow) {
            /** @var array $mapping */
            $mapping = $this->fileObject->current();

            $this->setFieldMapping($mapping);
            $this->fileObject->next();
        }
    }
}
