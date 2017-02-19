<?php

namespace Core;

use Core\Interfaces\ServicesLocator as ServicesLocatorInterface;

class ServicesLocator implements ServicesLocatorInterface
{

    protected $container;

    public function __construct(\ArrayAccess $container)
    {
        $this->container = $container;
    }

    /**
     * Проверка наличия ключа
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return $this->container->offsetExists($name);
    }

    /**
     * Получение значения по ключу
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->container->offsetGet($name) : $default;
    }
}