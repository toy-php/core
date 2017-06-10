<?php

namespace Core\Modules;

use Core\Exception;
use Core\WebApp;
use Core\Module;

class ConfigModule implements Module
{

    protected $config = [];
    protected $protected = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Получить значение ключа
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->config[$name] : $default;
    }

    /**
     * Установка конфигурационных значений
     * @param $name
     * @param $value
     * @param bool $protected
     * @throws Exception
     */
    public function set($name, $value, $protected = false)
    {
        if(in_array($name, $this->protected)){
            throw new Exception('Конфигурация защищена от перезаписи');
        }
        if($protected){
            $this->protected[] = $name;
        }
        $this->config[$name] = $value;
    }

    /**
     * Проверить наличие ключа
     * @param $name
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        $app['config'] = $this;
    }
}