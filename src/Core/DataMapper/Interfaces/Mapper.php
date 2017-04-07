<?php

namespace Core\DataMapper\Interfaces;

interface Mapper
{

    /**
     * Создание объекта сущности
     * @param array $data
     * @return Entity
     */
    public function createEntity(array $data = []);

    /**
     * Получить сущность по идентификатору
     * @param $id
     * @return Entity
     */
    public function getById($id);

    /**
     * Получить массив сещностей согласно критериям
     * @param array $criteria
     * @return array
     */
    public function getAll(array $criteria);

    /**
     * Сохранить данные сущности
     * @param Entity $entity
     * @return boolean
     */
    public function save(Entity $entity);

    /**
     * Удалить данные сущности
     * @param Entity $entity
     * @return boolean
     */
    public function delete(Entity $entity);
}