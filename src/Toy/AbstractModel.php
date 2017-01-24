<?php

namespace Toy;

/**
 * Class AbstractModel
 * @package Toy
 *
 * <p><b>События модели:</b></p>
 *
 * <p>model:fetch - получить данные модели из БД или другого хранилища</p>
 * <p>model:create - создать данные модели в БД или другом хранилище</p>
 * <p>model:update - изменить данные модели в БД или другом хранилище</p>
 * <p>model:delete - удалить данные модели в БД или другом хранилище</p>
 * <p>model:validation.error - ошибка валидации</p>
 *
 *
 */
abstract class AbstractModel extends AbstractArrayAccess
{

    /**
     * Имя первичного ключа
     * @var string
     */
    protected $primary_key = 'id';

    /**
     * Триггер ошибки валидации
     * @var bool
     */
    protected $validation_error = false;

    /**
     * Триггер состояния модели
     * @var bool
     */
    protected $changed = false;

    /**
     * Идентификатор модели
     * @var int
     */
    protected $model_id = 0;

    /**
     * Массив данных модели к взаимодействию с БД или другим источником данных
     * @var array
     */
    protected $attributes = [];

    /**
     * Массив данных по умолчанию
     * @var array
     */
    protected $defaults = [];

    /**
     * AbstractModel constructor.
     * @param array $defaults
     */
    public function __construct(array $defaults = [])
    {
        $this->defaults = $defaults;
        $this->attributes = $defaults;
    }

    /**
     * Получить имя первичного ключа
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primary_key;
    }

    /**
     * Установка триггера состояния модели,
     * меняет состояние на "неизменившуюся" модель
     * @param boolean $changed
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;
    }

    /**
     * Заполнение модели данными
     * @param array $data
     */
    public function fill(array $data)
    {
        $data = array_merge($this->defaults, $this->attributes, $data);
        $this->changed = array_udiff($this->attributes, $data, function ($a, $b) {
                return intval($a != $b);
            }) != array_udiff($data, $this->attributes, function ($a, $b) {
                return intval($a != $b);
            });
        $this->attributes = $data;
        $this->model_id = $this->has($this->primary_key) ? $this->get($this->primary_key) : 0;
    }

    /**
     * Валидация данных
     * @param array $data
     * @return array|false
     */
    public function validate(array $data)
    {
        return $data;
    }

    /**
     * Наличие атрибута
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Наличие атрибута
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Получение значения атрибута
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        return $this->has($name) ? $this->attributes[$name] : null;
    }

    /**
     * Получение значения атрибута
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Установка значения атрибута
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->fill([$name => $value]);
    }

    /**
     * Установка значения атрибута
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Удаление атрибута
     * @param $name
     */
    public function remove($name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * Очистка данных модели
     */
    public function clear()
    {
        $this->attributes = [];
    }

    /**
     * Получение состояния триггера наличия ошибки валидации
     * @return bool
     */
    public function hasValidationError()
    {
        return $this->validation_error;
    }

    /**
     * Состояние модели
     * @return bool
     */
    public function isChanged()
    {
        return $this->changed;
    }

    /**
     * Получение данных модели
     * @param array $options опции поиска данных модели, по умолчанию поиск по идентификатору
     */
    public function fetch(array $options = [])
    {
        $options = !empty($options)
            ? $options
            : ($this->model_id > 0 ? [$this->primary_key => $this->model_id] : []);
        if (empty($options)) {
            return;
        }
        $this->trigger('model:fetch', $options);
    }

    /**
     * Сохранение данных модели
     * Если нет идентификатора, то генерируется событие на создание данных
     * Так же сохраняются все дочерние модели
     */
    public function save()
    {
        if (!$this->isChanged()) {
            return;
        }
        $valid_data = $this->validate($this->attributes);
        if ($this->validation_error = (!is_array($valid_data) or empty($valid_data))) {
            $this->trigger('model:validation.error', $this->attributes);
            return;
        }
        $data_save = array_merge($this->defaults, array_intersect_key($valid_data, $this->defaults));
        if ($this->model_id > 0) {
            $this->trigger('model:update', $data_save);
        } else {
            $this->trigger('model:create', $data_save);
        }
        foreach ($this->storage as $model) {
            if ($model instanceof self
                or $model instanceof AbstractCollection
            ) {
                $model->save();
            }
        }
    }

    /**
     * Удаление данных модели
     */
    public function destroy()
    {
        if ($this->model_id > 0) {
            $this->trigger('model:delete', [$this->primary_key => $this->model_id]);
        }
    }

    /**
     * Преобразование модели в массив
     * @return array
     */
    public function toArray()
    {
        $array = $this->attributes;
        foreach ($this->storage as $key => $model) {
            if ($model instanceof self
                or $model instanceof AbstractCollection
            ) {
                $array[$key] = $model->toArray();
            }
        }
        return $array;
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
        $value = $this->offsetExists($offset) ? $this->storage[$offset] : null;
        if (!is_object($value) or !method_exists($value, '__invoke')) {
            return $value;
        }
        return $this->storage[$offset] = call_user_func($value, $this);
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
        if (!is_object($value) or !method_exists($value, '__invoke')) {
            throw new \InvalidArgumentException('Неверный тип данных');
        }
        $this->storage[$offset] = $value;
    }
}