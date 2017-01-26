<?php

namespace Core;

abstract class AbstractCollection extends AbstractSubject implements CollectionInterface
{

    /**
     * Позиция курсора итератора
     * @var int
     */
    protected $position = 0;

    /**
     * Прототип модели
     * @var ModelInterface
     */
    protected $prototype;


    /**
     * Массив моделей
     * @var array
     */
    protected $models = [];

    /**
     * AbstractCollection constructor.
     * @param ModelInterface $prototype
     */
    public function __construct(ModelInterface $prototype)
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
        return count($this->models);
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
        return $this->offsetExists($offset) ? $this->models[$offset] : null;
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
        $this->models[$offset] = $value;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->models[$offset]);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->models[$offset]);
    }

    /**
     * Фильтрация коллекции.
     * @param callable $function
     * @return AbstractCollection
     */
    public function filter(callable $function)
    {
        $new_collection = clone $this;
        $new_collection->models = array_filter($this->models, $function);
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
        $new_collection->models = array_map($function, $this->models);
        return $new_collection;
    }

    /**
     * Итеративно уменьшает коллекцию к единственному значению
     * @param callable $function
     * @return ModelInterface
     */
    public function reduce(callable $function)
    {
        return array_reduce($this->models, $function);
    }

    /**
     * Сортировка коллекции
     * @param callable $function
     */
    public function sort(callable $function)
    {
        usort($this->models, $function);
    }

    /**
     * Поиск модели по значению свойства
     * @param $property
     * @param $value
     * @return ModelInterface|null
     */
    public function search($property, $value)
    {
        $key = array_search($value, array_column($this->models, $property));
        return !empty($key) ? $this->models[$key] : null;
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
                $this->models[] = $model;
            }
        }
    }

    /**
     * Очистка коллекции моделей
     */
    public function clear()
    {
        $this->models = [];
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
        /** @var ModelInterface $model */
        foreach ($this->models as $model) {
            $model->save();
        }
    }

    /**
     * Удаление данных коллекции модели
     */
    public function destroy()
    {
        /** @var ModelInterface $model */
        foreach ($this->models as $model) {
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
        /** @var ModelInterface $model */
        foreach ($this->models as $model) {
            $result[] = $model->toArray();
        }
        return $result;
    }

}