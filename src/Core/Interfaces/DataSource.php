<?php

namespace Core\Interfaces;

interface DataSource extends Subject, Observer
{

    /**
     * Получить данные из источника, и заполнить модель
     * @param Model $model
     * @param array $criteria
     * @return void
     */
    public function fetch(Model $model, array $criteria);

    /**
     * Сохранить модель
     * @param Model $model
     * @return void
     */
    public function save(Model $model);

    /**
     * Удалить модель
     * @param Model $model
     * @return void
     */
    public function delete(Model $model);
}