<?php

namespace Core\DataMapper\Interfaces;

interface Entity extends \ArrayAccess, \IteratorAggregate
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
     * Расчет хеша сущности
     */
    public function calculateEntityHash();

    /**
     * Изменилась ли сущность
     * @return bool
     */
    public function isChange();

    /**
     * Заполнить сущность из массива
     * @param array $data
     */
    public function fill(array $data);

    /**
     * Преобразовать сущность в массив
     * @return array
     */
    public function toArray();

    /**
     * Рекурсивно преобразовать сущность в массив
     * @return array
     */
    public function recursiveToArray();
}