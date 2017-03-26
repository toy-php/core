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
    protected $tableName = '';

    /**
     * Имя первичного ключа
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Класс сущности
     * @var string
     */
    protected $entityClass;

    /**
     * @var ExtPDO
     */
    protected $extPdo;

    public function __construct(ExtPDO $extPdo,
                                $entityClass = Entity::class)
    {
        $this->extPdo = $extPdo;
        $this->entityClass = $entityClass;
    }

    /**
     * Создание объекта сущности
     * @param array $data
     * @return EntityInterface
     */
    private function _createEntity(array $data = [])
    {
        $entityClass = $this->entityClass;
        return new $entityClass($data);
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        $row = $this->extPdo->select($this->tableName, '*', [$this->primaryKey => $id]);
        if (empty($row)) {
            return null;
        }
        return $this->_createEntity($row);
    }

    /**
     * @inheritdoc
     */
    public function save(EntityInterface $entity)
    {
        if (!$entity instanceof $this->entityClass) {
            throw new CriticalException('Передана неверная сущность');
        }
        if ($entity->hasError()) {
            return false;
        }
        if ($entity->{$this->primaryKey} > 0) {
            return $this->update($entity);
        }
        return $this->insert($entity);
    }

    /**
     * Обновить данные сущности
     * @param EntityInterface $entity
     * @return bool
     */
    protected function update(EntityInterface $entity)
    {
        $data = $entity->toArray();
        if ($result = $this->extPdo->update($this->tableName, $data,
            [
                $this->primaryKey => $entity->{$this->primaryKey}
            ])
        ) {
            $entity->calculateEntityHash();
        }
        return $result;
    }

    /**
     * Сохранить данные новой сущности
     * @param EntityInterface $entity
     * @return bool
     */
    protected function insert(EntityInterface $entity)
    {
        $data = $entity->toArray();
        if ($this->extPdo->insert($this->tableName, $data)) {
            $id = $this->extPdo->lastInsertId($this->primaryKey);
            $entity->{$this->primaryKey} = $id;
            $entity->calculateEntityHash();
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete(EntityInterface $entity)
    {
        if (!$entity instanceof $this->entityClass) {
            throw new CriticalException('Передана неверная сущность');
        }
        if ($entity->hasError()) {
            return false;
        }
        if ($entity->{$this->primaryKey} > 0) {
            return $this->extPdo->delete($this->tableName,
                [
                    $this->primaryKey => $entity->{$this->primaryKey}
                ]);
        }
        return false;
    }

}