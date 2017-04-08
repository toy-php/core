<?php

namespace Core\DataMapper\Interfaces;

interface Collection extends \ArrayAccess
{

    /**
     * Проверяет наличие сущности в коллекции по идентификатору,
     * если сущность отсутствует, ищет в источнике и добавляет её в коллекцию
     * @param mixed $id
     * @return bool
     */
    public function offsetExists($id);

    /**
     * Удаляет сущность из коллекции и из источника по идентификатору
     * @param mixed $id
     */
    public function offsetUnset($id);

    /**
     * Сохраняет сущность в коллекции и в источнике
     * @param mixed $id
     * @param \Core\DataMapper\Interfaces\Entity $entity
     */
    public function offsetSet($id, $entity);

    /**
     * Получает сущность из коллекции по идентификатору,
     * если отсутствует, ищет в источнике и добавляет в коллекцию
     * @param string $id
     * @return \Core\DataMapper\Interfaces\Entity|null
     */
    public function offsetGet($id);

    /**
     * Находит сущности в источнике согласно критериям
     * дополняет текущую коллекцию, и возвращает коллекцию найденых
     * @param array $criteria
     * @return static
     */
    public function findAll(array $criteria);

    /**
     * Фильтрация коллекции.
     * Обходит каждую сущность коллекции,
     * передавая его в callback-функцию.
     * Если callback-функция возвращает true,
     * данная сущность из текущей коллекции попадает в результирующую коллекцию.
     * @param callable $function
     * @return static
     */
    public function filter(callable $function);

    /**
     * Перебор всех моделей коллекции.
     * Возвращает новую коллекцию,
     * содержащую сущности после их обработки callback-функцией.
     * @param callable $function
     * @return static
     */
    public function map(callable $function);

    /**
     * Итеративно уменьшает коллекцию к единственному значению
     * @param callable $function
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $function, $initial = null);

    /**
     * Сортировка коллекции
     * @param callable $function
     */
    public function sort(callable $function);

    /**
     * Поиск сущности по значению свойства
     * @param $property
     * @param $value
     * @return mixed
     */
    public function search($property, $value);

    /**
     * Поиск сущности согласно критериям
     * @param array $criteria
     * @return mixed|null
     */
    public function searchByCriteria(array $criteria);

    /**
     * Очистка коллекции
     */
    public function clear();

    /**
     * Количество сущностей в коллекции
     * @return int
     */
    public function collectionCount();

    /**
     * Количество сущностей в источнике согласно критериям
     * @param array $criteria
     * @return int
     */
    public function totalCount(array $criteria);
}