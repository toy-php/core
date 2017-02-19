<?php

namespace Core\Interfaces;

interface ServicesLocator
{

    /**
     * Получить сервис или конфигурацию
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * Проверить наличие сервиса или конфигурации
     * @param $name
     * @return boolean
     */
    public function has($name);

}