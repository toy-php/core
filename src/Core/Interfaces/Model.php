<?php

namespace Core\Interfaces;

interface Model extends \ArrayAccess, \IteratorAggregate, Subject, Observer
{

    /**
     * Получить сообщение ошибки
     * @return string
     */
    public function getErrorMessage();

    /**
     * Триггер наличия ошибки
     * @return boolean
     */
    public function hasError();

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
