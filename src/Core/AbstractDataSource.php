<?php

namespace Core;

use Core\Interfaces\Model;
use Core\Db\ExtPDO;

abstract class AbstractDataSource extends EventObject
{

    /**
     * Маршруты событий
     * @return array
     */
    public function getEventRouts()
    {
        $eventRouts = parent::getEventRouts();
        $eventRouts[ModelEvents::EVENT_FETCH] = [$this, 'fetch'];
        $eventRouts[ModelEvents::EVENT_SAVE] = [$this, 'save'];
        $eventRouts[ModelEvents::EVENT_DELETE] = [$this, 'delete'];
        return $eventRouts;
    }

    /**
     * Метод должен реализовать получение данных и заполнить модель
     * @param Model $subject
     * @param array $options
     * @return void
     */
    abstract public function fetch($subject, $options);

    /**
     * Сохранить данные модели
     * @param Model $subject
     * @param array $options
     * @return void
     */
    abstract public function save($subject, $options);

    /**
     * Удалить данные модели
     * @param Model $subject
     * @param array $options
     * @return void
     */
    abstract public function delete($subject, $options);

}