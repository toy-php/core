<?php

namespace Core;

use Core\Exceptions\CriticalExceptions;
use Core\Interfaces\Model as ModelInterface;
use Core\Interfaces\ServicesLocator;

class Model extends EventObject implements ModelInterface
{

    protected $components;
    protected $identityMap;
    protected $serviceLocator;

    /**
     * @inheritdoc
     */
    public function __construct(ServicesLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->components = new \ArrayObject();
        $this->identityMap = new \SplObjectStorage();
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        $path = explode('\\', static::class);
        return array_pop($path);
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
            throw new CriticalExceptions('Значение аргумента не реализует необходимый интерфейс');
        }
        if (!$this->identityMap->contains($value)) {
            $this->identityMap->attach($value, $value->getName());
            $this->components->offsetSet($offset, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            throw new CriticalExceptions(
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