<?php

namespace Core;

abstract class AbstractView extends AbstractObserver implements ViewInterface
{

    /**
     * Конвертер представления в строку
     * @var ConverterInterface
     */
    protected $converter;

    /**
     * Данные представления
     * @var array
     */
    protected $data = [];

    /**
     * Массив связанных моедлей
     * @var array
     */
    protected $views = [];

    /**
     * AbstractView constructor.
     * @param ConverterInterface $converter
     */
    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Преобразование структуры представления в массив
     * @return array
     */
    public function toArray()
    {
        $result = $this->data;
        /** @var ViewInterface $view */
        foreach ($this->views as $name => $view) {
            $result[$name] = $view->toArray();
        }
        return $result;
    }

    /**
     * Получение представления в виде строки
     * @return string
     */
    public function __toString()
    {
        return $this->converter->convert($this);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->views[$offset] : null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof ViewInterface) {
            throw new \InvalidArgumentException('Неверный тип объекта');
        }
        $this->views[$offset] = $value;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->views[$offset]);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->views[$offset]);
    }
}