<?php

namespace Core\DataMapper\Interfaces;

interface Mapper
{

    /**
     * Получить сущность по идентификатору
     * @param $id
     * @return Entity
     */
    public function getById($id);

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