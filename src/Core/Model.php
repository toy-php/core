<?php

namespace Core;

use Core\Exceptions\CriticalException;
use Core\Interfaces\Model as ModelInterface;

class Model extends EventObject implements ModelInterface
{

    /**
     * Сообщение ошибки
     * @var string
     */
    protected $errorMessage = '';

    /**
     * Триггер ошибки
     * @var boolean
     */
    protected $isError = false;

    protected $components;
    protected $identityMap;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->components = new \ArrayObject();
        $this->identityMap = new \SplObjectStorage();
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
        return $this->isError;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->components->getIterator();
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof ModelInterface) {
            throw new CriticalException('Значение аргумента не реализует необходимый интерфейс');
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
                printf('Компонент не содержит вложенного компонента "%s"', $offset));
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


}
