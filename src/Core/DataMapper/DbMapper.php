<?php

namespace Core\DataMapper;

use Core\Exceptions\CriticalException;
use Core\DataMapper\Interfaces\Mapper as MapperInterface;
use Core\DataMapper\Interfaces\Entity as EntityInterface;

class DbMapper implements MapperInterface
{

    /**
     * Имя таблицы
     * @var string
     */
    protected $tableName = 'tokens';

    /**
     * Класс сущности
     * @var string
     */
    protected $entityClass;

    /**
     * @var ExtPDO
     */
    protected $extPdo;

    /**
     * Мета данные таблицы
     * @var array
     */
    protected $tableMeta;

    /**
     * @var callable
     */
    protected $builder;

    public function __construct(ExtPDO $extPdo,
                                $entityClass = Entity::class)
    {
        $this->extPdo = $extPdo;
        $this->entityClass = $entityClass;
        $this->builder = function ($entity) {
            return $entity;
        };
    }

    /**
     * @inheritdoc
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    public function setEntityBuilder(callable $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Постройка объекта сущности
     * @param array $data
     * @return EntityInterface
     */
    protected function buildEntity(array $data = [])
    {
        $entityClass = $this->entityClass;
        $build = $this->builder;
        return $build(new $entityClass($data));
    }

    /**
     * Создание объекта сущности
     * @param array $data
     * @return EntityInterface
     * @throws CriticalException
     */
    public function createEntity(array $data = [])
    {
        $entity = $this->buildEntity($data);
        if ($this->insert($entity)) {
            return $entity;
        }
        throw new CriticalException('Возникла ошибка при сохранении сущности');
    }

    /**
     * @inheritdoc
     */
    public function count(array $criteria)
    {
        /** @var EntityInterface $entityClass */
        $entityClass = $this->getEntityClass();
        $pk = $entityClass::getPrimaryKey() ?: '*';
        $num = $this->extPdo->select($this->tableName, 'count(' . $pk . ')', $criteria)
            ->fetch(\PDO::FETCH_COLUMN);
        return filter_var($num, FILTER_VALIDATE_INT);
    }

    /**
     * @inheritdoc
     */
    public function getByCriteria(array $criteria)
    {
        $columns = $this->getColumns($criteria);
        $row = $this->extPdo->select($this->tableName, $columns, $criteria)
            ->fetch(\PDO::FETCH_ASSOC);
        if (empty($row)) {
            return null;
        }
        return $this->buildEntity($row);
    }

    /**
     * @inheritdoc
     */
    public function getAllByCriteria(array $criteria)
    {
        $columns = $this->getColumns($criteria);
        $rows = $this->extPdo->select($this->tableName, $columns, $criteria)
            ->fetchAll(\PDO::FETCH_ASSOC);
        $collection = [];
        foreach ($rows as $row) {
            $collection[] = $this->buildEntity($row);
        }
        return $collection;
    }

    /**
     * Получить поля для выборки
     * @param array $criteria
     * @return array|mixed
     */
    protected function getColumns(array $criteria)
    {
        $tableMeta = $this->getTableMeta();
        return isset($criteria['COLUMNS'])
            ? $criteria['COLUMNS']
            : [$this->tableName => array_column($tableMeta, 'Field')];
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        /** @var EntityInterface $entityClass */
        $entityClass = $this->entityClass;
        return $this->getByCriteria([
            $entityClass::getPrimaryKey() => $id
        ]);
    }

    /**
     * @inheritdoc
     */
    public function save(EntityInterface $entity)
    {
        if (!$entity instanceof $this->entityClass) {
            throw new CriticalException('Передана неверная сущность');
        }
        if ($entity->getId() > 0) {
            if ($this->update($entity)) {
                return true;
            }
            throw new CriticalException('Возникла ошибка при сохранении сущности');
        }
        if ($this->insert($entity)) {
            return true;
        }
        throw new CriticalException('Возникла ошибка при сохранении сущности');
    }

    /**
     * Получить мета данные таблицы
     * @return array
     */
    protected function getTableMeta()
    {
        return $this->tableMeta = !empty($this->tableMeta)
            ? $this->tableMeta
            : $this->extPdo->query('SHOW COLUMNS FROM ' . $this->tableName)
                ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Фильтрация входных данных
     * @param array $data
     * @return array
     */
    protected function filterData(array $data)
    {
        $tableMeta = $this->getTableMeta();
        $fields = array_column($tableMeta, 'Field');
        return array_filter($data, function ($key) use ($fields) {
            return in_array($key, $fields);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Обновить данные сущности
     * @param EntityInterface $entity
     * @return bool
     */
    protected function update(EntityInterface $entity)
    {
        return $this->extPdo->action(function (ExtPDO $extPDO) use ($entity){
            $data = $this->filterData($entity->toArray());
            return $extPDO->update($this->tableName, $data, [
                $entity->getPrimaryKey() => $entity->getId()
            ]);
        });
    }

    /**
     * Сохранить данные новой сущности
     * @param EntityInterface $entity
     * @return bool
     */
    protected function insert(EntityInterface $entity)
    {
        return $this->extPdo->action(function (ExtPDO $extPDO) use ($entity){
            $data = $this->filterData($entity->toArray());
            if ($extPDO->insert($this->tableName, $data)) {
                $id = $extPDO->lastInsertId($entity->getPrimaryKey());
                $entity[$entity->getPrimaryKey()] = $id;
                return true;
            }
            return false;
        });
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        if (!$entity instanceof $this->entityClass) {
            throw new CriticalException('Передана неверная сущность');
        }
        if ($entity->getId() > 0) {
            return $this->extPdo->action(function (ExtPDO $extPDO) use($entity){
                return $extPDO->delete($this->tableName, [
                    $entity->getPrimaryKey() => $entity->getId()
                ]);
            });
        }
        return false;
    }

}