<?php

namespace Toy;

abstract class AbstractCollection extends AbstractArrayAccess implements \Iterator, \Countable
{

    /**
     * Позиция курсора итератора
     * @var int
     */
    protected $position = 0;

    /**
     * Прототип модели
     * @var AbstractModel
     */
    protected $prototype;

    /**
     * AbstractCollection constructor.
     * @param AbstractModel $prototype
     */
    public function __construct(AbstractModel $prototype)
    {
        $this->prototype = $prototype;
    }

    /**
     * Клонирование коллекции
     */
    public function __clone()
    {
        $this->position = 0;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->offsetGet($this->position);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->offsetExists($this->position);
    }

    /**
     * Количество моделей в коллекции
     * @return int
     */
    public function count()
    {
        return count($this->storage);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->storage[$offset] : null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof $this->prototype) {
            throw new \InvalidArgumentException('Неверный тип объекта');
        }
        $this->storage[$offset] = $value;
    }

    /**
     * Фильтрация коллекции.
     * @param callable $function
     * @return AbstractCollection
     */
    public function filter(callable $function)
    {
        $new_collection = clone $this;
        $new_collection->storage = array_filter($this->storage, $function);
        return $new_collection;
    }

    /**
     * Перебор всех моделей коллекции
     * @param callable $function
     * @return AbstractCollection
     */
    public function map(callable $function)
    {
        $new_collection = clone $this;
        $new_collection->storage = array_map($function, $this->storage);
        return $new_collection;
    }

    /**
     * Итеративно уменьшает коллекцию к единственному значению
     * @param callable $function
     * @return AbstractModel
     */
    public function reduce(callable $function)
    {
        return array_reduce($this->storage, $function);
    }

    /**
     * Сортировка коллекции
     * @param callable $function
     */
    public function sort(callable $function)
    {
        usort($this->storage, $function);
    }

    /**
     * Поиск модели по значению свойства
     * @param $property
     * @param $value
     * @return AbstractModel|null
     */
    public function search($property, $value)
    {
        $key = array_search($value, array_column($this->storage, $property));
        return !empty($key) ? $this->storage[$key] : null;
    }

    /**
     * Заполнение коллекции данными
     * @param array $data
     */
    public function fill(array $data)
    {
        foreach ($data as $datum) {
            if (is_array($datum)) {
                $model = clone $this->prototype;
                $model->fill($datum);
                $model->setChanged(false);
                $this->storage[] = $model;
            }
        }
    }

    /**
     * Очистка коллекции моделей
     */
    public function clear()
    {
        $this->storage = [];
    }

    /**
     * Получение коллекции
     * @param array $options
     */
    public function fetch(array $options = [])
    {
        if (empty($options)) {
            return;
        }
        $this->trigger('collection:fetch', $options);
    }

    /**
     * Сохранение данных коллекции моделей
     */
    public function save()
    {
        /** @var AbstractModel $model */
        foreach ($this->storage as $model) {
            $model->save();
        }
    }

    /**
     * Удаление данных коллекции модели
     */
    public function destroy()
    {
        /** @var AbstractModel $model */
        foreach ($this->storage as $model) {
            $model->destroy();
        }
    }

    /**
     * Преобразование коллекции в массив
     * @return array
     */
    public function toArray()
    {
        $result = [];
        /** @var AbstractModel $model */
        foreach ($this->storage as $model) {
            $result[] = $model->toArray();
        }
        return $result;
    }

}