<?php

namespace Core\DataMapper;

use Core\Exceptions\CriticalException;
use Core\Exceptions\ValidateException;
use Core\DataMapper\Interfaces\Entity as EntityInterface;
use SplObserver;
use SplSubject;

class Entity implements EntityInterface, \SplObserver, \SplSubject
{

    /**
     * Идентификатор сущности
     * @var int
     */
    public $id = 0;

    /**
     * Поля исключаемые из преобразования
     * @var array
     */
    protected $excludedFields = [];

    /**
     * Хеш сущности
     * @var string
     */
    protected $entityHash = '';

    /**
     * Сообщение ошибки
     * @var string
     */
    protected $errorMessage = '';

    /**
     * Вложенные модели
     * @var \ArrayObject
     */
    protected $components;

    /**
     * Карта вложенных моделей
     * @var \SplObjectStorage
     */
    protected $identityMap;

    /**
     * Массив наблюдателей за сущностью
     * @var \SplObjectStorage
     */
    protected $observers;

    /**
     * @inheritdoc
     */
    public function __construct(array $data = [])
    {
        $this->components  = new \ArrayObject();
        $this->identityMap = new \SplObjectStorage();
        $this->observers = new \SplObjectStorage();
        $this->fill($data);
        $this->calculateEntityHash();
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @inheritdoc
     */
    public function hasError()
    {
        return !empty($this->errorMessage);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->components->getIterator();
    }

    /**
     * Расчет хеша сущности
     */
    public function calculateEntityHash()
    {
        $this->entityHash = md5(json_encode($this->toArray()));
    }

    /**
     * Изменилась ли сущность
     * @return bool
     */
    public function isChange()
    {
        return $this->entityHash !== md5(json_encode($this->toArray()));
    }

    /**
     * Вспомогательный метод установки значения поля
     * @param $name
     * @param $value
     */
    private function _setMethod($name, $value)
    {
        $setMethod = 'set' . ucfirst($name);
        if (method_exists($this, $setMethod)) {
            $this->$setMethod($value);
        }
        if (property_exists($this, $name)
            and !in_array($name, $this->excludedFields)
        ) {
            $this->$name = $value;
        }
    }

    /**
     * Вспомогательный метод валидации значения поля
     * @param $name
     * @param $value
     * @return mixed
     */
    private function _validateMethod($name, $value)
    {
        $validateMethod = 'validate' . ucfirst($name);
        if (method_exists($this, $validateMethod)) {
            return $this->$validateMethod($value);
        }
        return $value;
    }

    /**
     * Заполнить сущность из массива
     * @param array $data
     */
    public function fill(array $data)
    {
        try {
            foreach ($data as $name => $value) {
                $this->_setMethod($name, $this->_validateMethod($name, $value));
            }
        } catch (ValidateException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Вспомогательный метод получения значения поля
     * @param \ReflectionProperty $property
     * @return array|bool
     */
    private function _getMethod(\ReflectionProperty $property)
    {
        $name = $property->getName();
        $value = $property->getValue($this);
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return [$name, $this->$method()];
        } elseif (!empty($value) and !in_array($name, $this->excludedFields)) {
            return [$name, $value];
        }
        return false;
    }

    /**
     * Преобразовать сущность в массив
     * @return array
     */
    public function toArray()
    {
        $entityReflect = new \ReflectionClass($this);
        $properties = $entityReflect->getProperties(\ReflectionProperty::IS_PUBLIC);
        $array = [];
        foreach ($properties as $property) {
            if ($value = $this->_getMethod($property)) {
                $array[$value[0]] = $this->_validateMethod($value[0], $value[1]);
            }
        }
        return $array;
    }

    /**
     * Рекурсивно преобразовать сущность в массив
     * @return array
     */
    public function recursiveToArray()
    {
        $array = $this->toArray();
        /** @var EntityInterface $entity */
        foreach ($this->getIterator() as $key => $entity) {
            $array[$key] = $entity->recursiveToArray();
        }
        return $array;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Entity) {
            throw new CriticalException('Значение аргумента не является сущностью');
        }
        if (!$this->identityMap->contains($value)) {
            $this->identityMap->attach($value);
            $this->components->offsetSet($offset, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new CriticalException(
                printf('Сущность не содержит вложенной сущности "%s"', $offset));
        }
        return $this->components->offsetGet($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->components->offsetExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $model = $this->offsetGet($offset);
            $this->identityMap->detach($model);
            $this->components->offsetUnset($offset);
        }
    }

    /**
     * Обновиться, если сущность является наблюдателем
     * @param SplSubject $subject
     * @return void
     */
    public function update(SplSubject $subject)
    {
        // TODO: Implement update() method.
    }

    /**
     * Attach an SplObserver
     * @link http://php.net/manual/en/splsubject.attach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to attach.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function attach(SplObserver $observer)
    {
        $this->observers->attach($observer);
    }

    /**
     * Detach an observer
     * @link http://php.net/manual/en/splsubject.detach.php
     * @param SplObserver $observer <p>
     * The <b>SplObserver</b> to detach.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function detach(SplObserver $observer)
    {
        $this->observers->detach($observer);
    }

    /**
     * Notify an observer
     * @link http://php.net/manual/en/splsubject.notify.php
     * @return void
     * @since 5.1.0
     */
    public function notify()
    {
        /** @var SplObserver $observer */
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}