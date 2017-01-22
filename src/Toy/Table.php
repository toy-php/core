<?php

namespace Toy;

use Toy\Components\Db\Db;
use Toy\Components\Db\QueryMode;
use Toy\Components\QueryBuilder\Delete;
use Toy\Components\QueryBuilder\Insert;
use Toy\Components\QueryBuilder\Select;
use Toy\Components\QueryBuilder\Update;

class Table extends AbstractObserver
{

    protected $events = [
        'collection:fetch' => 'collectionFetch',
        'model:fetch' => 'modelFetch',
        'model:create' => 'modelCreate',
        'model:update' => 'modelUpdate',
        'model:delete' => 'modelDelete'
    ];

    /**
     * Имя таблицы
     * @var string
     */
    protected $source;

    /**
     * @var Db
     */
    protected $db;

    /**
     * Table constructor.
     * @param \PDO $pdo
     * @param $source
     */
    public function __construct(\PDO $pdo, $source)
    {
        $this->db = new Db($pdo);
        $this->source = $source;
    }

    /**
     * Подготовка запроса на выборку
     * @param array $options
     * @return Select
     */
    protected function prepareSelect($options = [])
    {
        $query = new Select($this->source);
        if (isset($options['COLUMNS'])) {
            $query->columns($options['COLUMNS']);
        }
        $query->where($options['CRITERIA']);
        if (isset($options['ORDER'])) {
            $query->orderBy($options['ORDER']);
        }
        if (isset($options['LIMIT'])) {
            $limit = isset($options['LIMIT'][0]) ? $options['LIMIT'][0] : 50;
            $offset = isset($options['LIMIT'][1]) ? $options['LIMIT'][1] : 0;
            $query->limit($offset, $limit);
        }
        if (isset($options['JOIN'])) {
            $query->join($options['JOIN']);
        }
        return $query;
    }

    /**
     * Получение коллкции данных
     * @param AbstractSubject $subject
     * @param array $options
     */
    public function collectionFetch(AbstractSubject $subject, $event, array $options)
    {
        /** @var AbstractCollection $subject */
        $query = $this->prepareSelect($options);
        $data = $this->db->query($query->getSql())
            ->execute(QueryMode::FETCH_ALL, $query->getBindings());
        if (empty($data)) {
            $subject->trigger('collection:fetch.fail', $options);
            return;
        }
        $subject->fill($data);
        $subject->trigger('collection:fetch.success');
    }

    /**
     * Получение данных модели
     * @param AbstractSubject $subject
     * @param array $options
     */
    public function modelFetch(AbstractSubject $subject, $event, array $options)
    {
        /** @var AbstractModel $subject */
        $query = $this->prepareSelect($options);
        $data = $this->db->query($query->getSql())
            ->execute(QueryMode::FETCH, $query->getBindings());
        if (empty($data)) {
            $subject->trigger('model:fetch.fail', $options);
            return;
        }
        $subject->fill($data);
        $subject->trigger('model:fetch.success');
        $subject->setChanged(false);
    }

    /**
     * Сохранение данных модели
     * @param AbstractSubject $subject
     * @param array $options
     */
    public function modelCreate(AbstractSubject $subject, $event, array $options)
    {
        /** @var AbstractModel $subject */
        $query = new Insert($this->source);
        $query->data($options);
        $id = $this->db->query($query->getSql())
            ->execute(QueryMode::INSERT, $query->getBindings());
        if (empty($id)) {
            $subject->trigger('model:create.fail', $options);
            return;
        }
        $subject->set($subject->getPrimaryKey(), $id);
        $subject->setChanged(false);
        $subject->trigger('model:create.success');
    }

    /**
     * Изменение данных модели
     * @param AbstractSubject $subject
     * @param array $options
     */
    public function modelUpdate(AbstractSubject $subject, $event, array $options)
    {
        /** @var AbstractModel $subject */
        $query = new Update($this->source);
        $query->data($options);
        $query->where([$subject->getPrimaryKey() => $subject->get($subject->getPrimaryKey())]);
        $count_row = $this->db->query($query->getSql())
            ->execute(QueryMode::UPDATE, $query->getBindings());
        if (empty($count_row)) {
            $subject->trigger('model:update.fail', $options);
            return;
        }
        $subject->setChanged(false);
        $subject->trigger('model:update.success');
    }

    /**
     * Удаление данных модели
     * @param AbstractSubject $subject
     * @param array $options
     */
    public function modelDelete(AbstractSubject $subject, $event, array $options)
    {
        /** @var AbstractModel $subject */
        $query = new Delete($this->source);
        $query->where($options);
        $count_row = $this->db->query($query->getSql())
            ->execute(QueryMode::REMOVE, $query->getBindings());
        if (empty($count_row)) {
            $subject->trigger('model:delete.fail', $options);
            return;
        }
        $subject->clear();
        $subject->setChanged(false);
        $subject->trigger('model:delete.success');
    }

}