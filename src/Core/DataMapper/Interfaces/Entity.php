<?php

namespace Core\DataMapper\Interfaces;

interface Entity extends \ArrayAccess
{

    /**
     * Получить данные которые изменились
     * @return array
     */
    public function getChange();

    /**
     * Получить идентификатор сущности
     * @return mixed
     */
    public function getId();

    /**
     * Получить имя первичного ключа
     * @return string
     */
    public static function getPrimaryKey();

    /**
     * Преобразовать сущность в массив
     * @return array
     */
    public function toArray();

}