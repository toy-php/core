<?php

namespace Core\DataMapper;

use Core\Container\Container;
use Core\DataMapper\Interfaces\Mapper;
use Core\Exceptions\CriticalException;
use Core\DataMapper\Interfaces\Collection as CollectionInterface;

class Collection extends Container implements CollectionInterface
{

    /**
     * Преобразователь
     * @var Mapper
     */
    protected $mapper;

    /**
     * Тип сущностей коллекции
     * @var string
     */
    protected $type;

    public function __construct(Mapper $mapper)
    {
        parent::__construct([], false);
        $this->mapper = $mapper;
        $this->type = $mapper->getEntityClass();
    }

    /**
     * Создать новую сущность
     * @param array $data
     * @return Interfaces\Entity
     */
    public function createEntity(array $data)
    {
        return $this[] = $this->mapper->createEntity($data);
    }

    /**
     * Проверяет наличие сущности в коллекции по идентификатору,
     * если сущность отсутствует, ищет в источнике и добавляет её в коллекцию
     * @param mixed $id
     * @return bool
     */
    public function offsetExists($id)
    {
        if(is_null($id)){
            return false;
        }
        if(parent::offsetExists($id)){
            $this->offsetGet($id);
        }
        return parent::offsetExists($id);
    }

    /**
     * Удаляет сущность из коллекции и из источника по идентификатору
     * @param mixed $id
     */
    public function offsetUnset($id)
    {
        if(is_null($id)){
            return;
        }
        $entity = $this->offsetGet($id);
        if(!empty($entity)){
            $this->mapper->delete($entity);
        }
        parent::offsetUnset($id);
    }

    /**
     * Сохраняет сущность в коллекции и в источнике
     * @param mixed $id
     * @param \Core\DataMapper\Interfaces\Entity $entity
     * @throws CriticalException
     */
    public function offsetSet($id, $entity)
    {
        /** @var \Core\DataMapper\Interfaces\Entity $entity */
        if (!$entity instanceof $this->type) {
            throw new CriticalException('Неверная сущность');
        }
        if($entity->getId() > 0 and !is_null($id) and $id != $entity->getId()){
            throw new CriticalException('Указан неверный идентификатор сущности');
        }
        $this->mapper->save($entity);
        parent::offsetSet($entity->getId(), $entity);

    }

    /**
     * Получает сущность из коллекции по идентификатору,
     * если отсутствует, ищет в источнике и добавляет в коллекцию
     * @param string $id
     * @return \Core\DataMapper\Interfaces\Entity|null
     */
    public function offsetGet($id)
    {
        if(is_null($id)){
            return null;
        }
        if(!$this->values->offsetExists($id)){
            $entity = $this->mapper->getById($id);
            if(!empty($entity)){
                $this->values[$entity->getId()] = $entity;
            }
        }
        return parent::offsetGet($id);
    }

    /**
     * Находит сущности в источнике согласно критериям
     * дополняет текущую коллекцию, и возвращает коллекцию найденых
     * @param array $criteria
     * @return static
     */
    public function findAll(array $criteria)
    {
        $entities = $this->mapper->getAllByCriteria($criteria);
        /** @var \Core\DataMapper\Interfaces\Entity $entity */
        $new_collection = clone $this;
        foreach ($entities as $entity) {
            $this[$entity->getId()] = $entity;
            $new_collection[$entity->getId()] = $entity;
        }
        return $new_collection;
    }

    /**
     * Фильтрация коллекции.
     * Обходит каждую сущность коллекции,
     * передавая его в callback-функцию.
     * Если callback-функция возвращает true,
     * данная сущность из текущей коллекции попадает в результирующую коллекцию.
     * @param callable $function
     * @return static
     */
    public function filter(callable $function)
    {
        $new_collection = clone $this;
        $new_collection->values = new \ArrayObject(array_filter($this->values->getArrayCopy(), $function));
        return $new_collection;
    }

    /**
     * Перебор всех моделей коллекции.
     * Возвращает новую коллекцию,
     * содержащую сущности после их обработки callback-функцией.
     * @param callable $function
     * @return static
     */
    public function map(callable $function)
    {
        $new_collection = clone $this;
        $new_collection->values = new \ArrayObject(array_map($function, $this->values->getArrayCopy()));
        return $new_collection;
    }

    /**
     * Итеративно уменьшает коллекцию к единственному значению
     * @param callable $function
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $function, $initial = null)
    {
        return array_reduce($this->values->getArrayCopy(), $function, $initial);
    }

    /**
     * Сортировка коллекции
     * @param callable $function
     */
    public function sort(callable $function)
    {
        $this->values->uasort($function);
    }

    /**
     * Поиск сущности по значению свойства
     * @param $property
     * @param $value
     * @return mixed
     */
    public function search($property, $value)
    {
        $key = array_search($value, array_column($this->values->getArrayCopy(), $property));
        if(empty($key)){
            $entity = $this->searchByCriteria([$property => $value]);
            if(empty($entity)){
                return null;
            }
            return $entity;
        }
        return $this[$key];
    }

    /**
     * Поиск сущности согласно критериям
     * @param array $criteria
     * @return mixed|null
     */
    public function searchByCriteria(array $criteria)
    {
        $entity = $this->mapper->getByCriteria($criteria);
        if(!empty($entity)){
            if(!$this->values->offsetExists($entity->getId())){
                $this->offsetSet($entity->getId(), $entity);
            }
            return $this->offsetGet($entity->getId());
        }
        return null;
    }

    /**
     * Очистка коллекции
     */
    public function clear()
    {
        $this->values->exchangeArray([]);
        $this->frozen->exchangeArray([]);
    }

    /**
     * Количество сущностей в коллекции
     * @return int
     */
    public function collectionCount()
    {
        return $this->values->count();
    }

    /**
     * Количество сущностей в источнике согласно критериям
     * @param array $criteria
     * @return int
     */
    public function totalCount(array $criteria = [])
    {
        return $this->mapper->count($criteria);
    }
}