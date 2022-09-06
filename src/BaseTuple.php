<?php

namespace vladgtr\TimeDistributor;

use Exception;

class BaseTuple extends BaseList
{
    public function __construct(...$value)
    {
        parent::__construct(...$value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value): void
    {
        throw new Exception('Tuple unchangeable');
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Tuple unchangeable');
    }
}
