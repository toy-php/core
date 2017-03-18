<?php

namespace Core;

use Core\Db\ExtPDO;

abstract class AbstractDbDataSource extends AbstractDataSource
{

    /**
     * Имя первичного ключа
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Имя таблицы
     * @var string
     */
    protected $tableName = '';

    /**
     * @var ExtPDO
     */
    protected $db;

    function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Заполнить сущность данными
     * @param $entity
     * @param array $data
     * @return void
     */
    abstract public function fillEntity($entity, array $data);

    /**
     * Преобразовать сущность в массив
     * @param $entity
     * @return array
     */
    abstract public function entityToArray($entity);

    /**
     * Метод должен реализовать получение данных и заполнить модель
     * @param AbstractEntity $subject
     * @param array $options
     * @return void
     */
    public function fetch($subject, $options)
    {
        if(!empty($subject->id)){
            return;
        }
        $subject->trigger(ModelEvents::EVENT_BEFORE_FETCH);
        $stmt = $this->db->select($this->tableName, '*', $options);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        if(empty($data)){
            return;
        }
        $this->fillEntity($subject, $data);
        $subject->trigger(ModelEvents::EVENT_AFTER_FETCH);
    }

    /**
     * Сохранить данные модели
     * @param AbstractEntity $subject
     * @param array $options
     * @return void
     */
    public function save($subject, $options)
    {
        $data = $this->entityToArray($subject);
        $subject->trigger(ModelEvents::EVENT_BEFORE_SAVE);
        if($subject->id > 0){
            $this->db->update($this->tableName, $data, [$this->primaryKey => $subject->id]);
            $subject->trigger(ModelEvents::EVENT_AFTER_SAVE);
            return;
        }
        if($this->db->insert($this->tableName, $data)){
            $subject->id = $this->db->lastInsertId();
        }
        $subject->trigger(ModelEvents::EVENT_AFTER_SAVE);
    }

    /**
     * Удалить данные модели
     * @param AbstractEntity $subject
     * @param array $options
     * @return void
     */
    public function delete($subject, $options)
    {
        if($subject->id > 0){
            $subject->trigger(ModelEvents::EVENT_BEFORE_DELETE);
            $this->db->delete($this->tableName, [$this->primaryKey => $subject->id]);
            $subject->trigger(ModelEvents::EVENT_AFTER_DELETE);
        }
    }

}
