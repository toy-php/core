<?php

namespace Core;

/**
 * Class AbstractModel
 * @package Core
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
abstract class AbstractModel extends AbstractSubject implements ModelInterface
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
     * Массив связанных моедлей
     * @var array
     */
    protected $models = [];

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
     * Метод выполняемый перед созданием записи
     * @param array $data
     * @return array
     */
    public function beforeCreate(array $data)
    {
        return $data;
    }

    /**
     * Метод выполняемый перед обновлением записи
     * @param array $data
     * @return array
     */
    public function beforeUpdate(array $data)
    {
        return $data;
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
            $this->trigger('model:update', $this->beforeUpdate($data_save));
        } else {
            $this->trigger('model:create', $this->beforeCreate($data_save));
        }
        foreach ($this->models as $model) {
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
        foreach ($this->models as $key => $model) {
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
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        $value = $this->offsetExists($offset) ? $this->models[$offset] : null;
        $model = (!is_object($value) or !method_exists($value, '__invoke'))
            ? $value
            : $this->models[$offset] = call_user_func($value, $this);
        if(!$model instanceof ModelInterface){
            throw new \Exception('Объект не реализует необходимый интерфейс');
        }
        return $model;
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
}