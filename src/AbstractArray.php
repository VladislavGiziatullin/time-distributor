<?php

namespace vladgtr\TimeDistributor;

use ArrayAccess;
use Countable;
use Iterator;

abstract class AbstractArray implements ArrayAccess, Countable, Iterator
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }


    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->data[$this->position];
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return isset($this->data[$this->position]);
    }
}
