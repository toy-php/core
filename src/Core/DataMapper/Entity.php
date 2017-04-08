<?php

namespace Core\DataMapper;

use Core\Container\Container;
use Core\DataMapper\Interfaces\Entity as EntityInterface;

class Entity extends Container implements EntityInterface
{

    protected static $primaryKey = 'id';

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

}