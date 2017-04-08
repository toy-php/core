<?php

namespace Core\Container;

use Core\Exceptions\CriticalException;

class Container implements \ArrayAccess
{
    /**
     * @var \ArrayObject
     */
    protected $frozen;

    /**
     * @var \ArrayObject
     */
    protected $values;

    /**
     * @var \SplObjectStorage
     */
    protected $factories;

    public function __construct(array $defaults = [])
    {
        $this->frozen = new \ArrayObject();
        $this->values = new \ArrayObject($defaults);
        $this->factories = new \SplObjectStorage();
    }

    /**
     * Проверка защищенности значения от изменений
     * @param $name
     * @throws CriticalException
     */
    private function checkFrozen($name)
    {
        if($this->frozen->offsetExists($name)){
            throw new CriticalException(
                printf('Параметр "%s" был использован и теперь защищен от изменения', $name)
            );
        }
    }

    /**
     * Добавление параметра в контейнер
     * @param string $name
     * @param mixed $value
     * @throws CriticalException
     */
    public function offsetSet($name, $value)
    {
        if(is_null($name)){
            $this->values[] = $value;
            return;
        }
        $this->checkFrozen($name);
        $this->values[$name] = $value;
    }

    /**
     * Получение значения параметра по ключу
     * @param string $name
     * @return mixed|null
     */
    public function offsetGet($name)
    {
        $value = $this->offsetExists($name)
            ? $this->frozen[$name] = $this->values[$name]
            : null;
        if (!is_object($value) || !method_exists($value, '__invoke')) {
            return $value;
        }
        return (isset($this->factories[$value]))
            ? $value($this)
            : $this->values[$name] = $value($this);
    }

    /**
     * Наличие параметра в контейнере
     * @param string $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->values[$name]);
    }

    /**
     * Удаление параметра из контейнера
     * @param mixed $name
     */
    public function offsetUnset($name)
    {
        $this->checkFrozen($name);
        if ($this->offsetExists($name)) {
            $value = $this->raw($name);
            if (!is_object($value) || !method_exists($value, '__invoke')) {
                $this->factories->detach($value);
            }
            $this->values->offsetUnset($name);
        }
    }

    /**
     * Получение сырых данных параметра по ключу
     * @param string $name
     * @return mixed
     * @throws CriticalException
     */
    public function raw($name)
    {
        if (!$this->offsetExists($name)) {
            throw new CriticalException(sprintf('Ключ "%s" не найден', $name));
        }
        return $this->values[$name];
    }

    /**
     * Объявление функции параметра фабрикой
     * @param $callable
     * @return mixed
     * @throws CriticalException
     */
    public function factory($callable)
    {
        if (!method_exists($callable, '__invoke')) {
            throw new CriticalException('Неверная функция');
        }
        $this->factories->attach($callable);
        return $callable;
    }

    /**
     * Преобразование содержимого контейнера в массив
     * @return array
     */
    public function toArray()
    {
        $values = $this->values->getArrayCopy();
        $result = [];
        foreach ($values as $key => $value) {
            if($value instanceof static){
                $result[$key] = $value->toArray();
            }else{
                $result[$key] = $this[$key];
            }
        }
        return $result;
    }
}