<?php

namespace vladgtr\TimeDistributor;

class BaseList extends AbstractArray
{
    public function __construct(...$values)
    {
        foreach ($values as $value) {
            $this->data[] = $value;
        }
    }
}
