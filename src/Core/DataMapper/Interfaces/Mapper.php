<?php

namespace Core\DataMapper\Interfaces;

interface Mapper
{

    /**
     * Получить имя класса сущности
     * @return string
     */
    public function getEntityClass();

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
     * Получить сущность согласно критериям
     * @param array $criteria
     * @return Entity
     */
    public function getByCriteria(array $criteria);

    /**
     * Получить количество строк согласно критериям
     * @param array $criteria
     * @return integer
     */
    public function count(array $criteria);

    /**
     * Получить массив сещностей согласно критериям
     * @param array $criteria
     * @return array
     */
    public function getAllByCriteria(array $criteria);

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