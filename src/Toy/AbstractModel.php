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
     * Фильтр первичного ключа
     * @var int
     */
    protected $primary_key_filter = FILTER_VALIDATE_INT;

    /**
     * Триггер ошибки валидации
     * @var bool
     */
    protected $validation_error = false;

    /**
     * События генерируемые при возникновении ошибок валидации
     * @var array
     */
    protected $validation_events = [];

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
     * Массив всех данных модели
     * @var array
     */
    protected $all_attributes = [];

    /**
     * Доступные поля модели к взаимодействию с БД или другим источником данных
     * @var array
     */
    protected $available_fields = [];

    /**
     * AbstractModel constructor.
     * @param array $defaults
     */
    public function __construct(array $defaults = [])
    {
        $this->available_fields = array_merge([$this->primary_key => $this->primary_key_filter],
            $this->available_fields);
        $default_data = array_fill_keys(array_keys($this->available_fields), null);
        $this->attributes = array_merge($default_data, array_intersect_key($defaults, $default_data));
        $this->all_attributes = array_merge($this->attributes, $defaults);
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
     */
    public function unchanged()
    {
        $this->changed = false;
    }

    /**
     * Заполнение модели данными
     * @param array $data
     */
    public function fill(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Наличие атрибута
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->all_attributes[$name]);
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
        return $this->has($name) ? $this->all_attributes[$name] : null;
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
        $this->all_attributes[$name] = $value;
        if(!isset($this->available_fields[$name])){
            return;
        }
        $filter = $this->available_fields[$name];
        $filtered_value = filter_var($value, $filter);
        if($this->validation_error === false and $filtered_value != $value){
            $this->validation_error = true;
            if (isset($this->validation_events[$name])){
                $this->trigger($this->validation_events[$name]);
            }
        }
        if($this->changed === false and $this->attributes[$name] != $filtered_value){
            $this->changed = true;
        }
        if ($name == $this->primary_key) {
            $this->model_id = $filtered_value;
        }
        $this->attributes[$name] = $filtered_value;
        $this->all_attributes[$name] = $filtered_value;
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
        unset($this->all_attributes[$name]);
        $this->changed = true;
    }

    /**
     * Очистка данных модели
     */
    public function clear()
    {
        $this->attributes = [];
        $this->all_attributes = [];
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
     * Получение данных модели
     * @param array $options опции поиска данных модели, по умолчанию поиск по идентификатору
     */
    public function fetch(array $options = [])
    {
        $options = !empty($options)
            ? $options
            : ($this->model_id > 0 ? [$this->primary_key => $this->model_id] : []);
        if (empty($options)){
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
        if($this->hasValidationError()){
            $this->trigger('model:validation.error', $this->attributes);
            return;
        }
        if($this->changed){
            if($this->model_id > 0){
                $this->trigger('model:update', array_diff($this->attributes, ['']));
            }else{
                $this->trigger('model:create', $this->attributes);
            }
        }
        foreach ($this->storage as $model) {
            if($model instanceof self
                or $model instanceof AbstractCollection){
                $model->save();
            }
        }
    }

    /**
     * Удаление данных модели
     */
    public function destroy()
    {
        if($this->model_id > 0){
            $this->trigger('model:delete', [$this->primary_key => $this->model_id]);
        }
    }

    /**
     * Преобразование модели в массив
     * @return array
     */
    public function toArray()
    {
        $array = $this->all_attributes;
        foreach ($this->storage as $key => $model) {
            if($model instanceof self
                or $model instanceof AbstractCollection){
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
        if (!is_object($value) or !method_exists($value, '__invoke')){
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
        if (!is_object($value) or !method_exists($value, '__invoke')){
            throw new \InvalidArgumentException('Неверный тип данных');
        }
        $this->storage[$offset] = $value;
    }
}