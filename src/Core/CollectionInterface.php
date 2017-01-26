<?php

namespace Core;

interface CollectionInterface extends SubjectInterface, \ArrayAccess, \Iterator, \Countable
{

    /**
     * Фильтрация коллекции.
     * @param callable $function
     * @return AbstractCollection
     */
    public function filter(callable $function);

    /**
     * Перебор всех моделей коллекции с применением функции к каждому элементу
     * @param callable $function
     * @return AbstractCollection
     */
    public function map(callable $function);

    /**
     * Итеративно уменьшает коллекцию к единственному значению
     * @param callable $function
     * @return AbstractModel
     */
    public function reduce(callable $function);

    /**
     * Сортировка коллекции
     * @param callable $function
     */
    public function sort(callable $function);

    /**
     * Поиск модели по значению свойства
     * @param $property
     * @param $value
     * @return AbstractModel|null
     */
    public function search($property, $value);

    /**
     * Заполнение коллекции данными
     * @param array $data
     */
    public function fill(array $data);

    /**
     * Очистка коллекции моделей
     */
    public function clear();

    /**
     * Получение коллекции
     * @param array $options
     */
    public function fetch(array $options = []);

    /**
     * Сохранение данных коллекции моделей
     */
    public function save();

    /**
     * Удаление данных коллекции модели
     */
    public function destroy();

    /**
     * Преобразование коллекции в массив
     * @return array
     */
    public function toArray();
}