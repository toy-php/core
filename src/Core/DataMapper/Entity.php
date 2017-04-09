<?php

namespace Core\DataMapper;

use Core\Container\Container;
use Core\DataMapper\Interfaces\Entity as EntityInterface;

class Entity extends Container implements EntityInterface
{

    protected static $primaryKey = 'id';
    protected $change;

    public function __construct(array $defaults = [], $frozenValues = true)
    {
        parent::__construct($defaults, $frozenValues);
        $this->change = new \ArrayObject();
    }

    public function offsetSet($name, $value)
    {
        parent::offsetSet($name, $value);
        $this->change[$name] = $value;
    }

    /**
     * Получить данные которые изменились
     * @return array
     */
    public function getChange()
    {
        return $this->change->getArrayCopy();
    }

    /**
     * Получить идентификатор сущности
     * @return mixed
     */
    public function getId()
    {
        return $this[self::$primaryKey];
    }

    /**
     * Получить имя первичного ключа
     * @return string
     */
    public static function getPrimaryKey()
    {
        return self::$primaryKey;
    }

    /**
     * Установка значения
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($this->underscoreToCamelCase($name));
        if (method_exists($this, $method)) {
            $this->$method($value);
            return;
        }
        $this[$name] = $value;
    }

    /**
     * Получение значения
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($this->underscoreToCamelCase($name));
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this[$name];
    }

    /**
     * Удаление значения
     * @param $name
     */
    public function __unset($name)
    {
        unset($this[$name]);
    }

    /**
     * Наличие значения
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this[$name]);
    }

    /**
     * Преобразование имени
     * @param $name
     * @return string
     */
    private function underscoreToCamelCase($name)
    {
        if (strpos($name, '_')) {
            $name = implode('',
                array_map('ucfirst',
                    array_map('strtolower',
                        explode('_', $name))));
        }
        return $name;
    }
}