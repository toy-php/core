<?php

namespace Core\Interfaces;

interface Model extends \ArrayAccess, \IteratorAggregate, Subject, Observer
{

    /**
     * @param mixed $offset
     * @return Model
     */
    public function offsetGet($offset);

    /**
     * @param mixed $offset
     * @param Model $value
     */
    public function offsetSet($offset, $value);

}