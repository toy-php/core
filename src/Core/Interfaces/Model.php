<?php

namespace Core\Interfaces;

interface Model extends \ArrayAccess, \IteratorAggregate, Subject, Observer
{

    /**
     * Model constructor.
     * @param ServicesLocator $serviceLocator
     */
    public function __construct(ServicesLocator $serviceLocator);

    /**
     * Получить имя модели
     * @return string
     */
    public function getName();

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