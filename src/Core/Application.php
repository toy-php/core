<?php

namespace Core;

use Core\Interfaces\Application as ApplicationInterface;
use Core\Exceptions\CriticalException;
use Core\Interfaces\ExceptionsHandler;
use Core\Interfaces\Router;
use Psr\Log\LoggerInterface;

class Application extends Module implements ApplicationInterface
{
    /**
     * Текущий режим работы приложения
     * @var string
     */
    protected $mode = null;

    /**
     * @var ExceptionsHandler
     */
    protected $exceptionHandler;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Получение экземпляра приложения с соответствующим маршрутизатором
     * @param Router $router
     * @return $this|Application
     */
    public function withRouter(Router $router)
    {
        if ($this->router === $router) {
            return $this;
        }
        $instance = clone $this;
        $instance->router = $router;
        return $instance;
    }

    /**
     * Получение экземпляра приложения с соответствующим обработчиком ошибок
     * @param ExceptionsHandler $exceptionHandler
     * @return $this|Application
     */
    public function withExceptionHandler(ExceptionsHandler $exceptionHandler)
    {
        if ($this->exceptionHandler === $exceptionHandler) {
            return $this;
        }
        $instance = clone $this;
        $instance->exceptionHandler = $exceptionHandler;
        return $instance;
    }

    /**
     * Получение экземпляра приложения с соответствующим режимом
     * @param $mode
     * @return $this|Application
     */
    public function withMode($mode)
    {
        if ($this->mode === $mode) {
            return $this;
        }
        $instance = clone $this;
        $instance->mode = $mode;
        return $instance;
    }

    /**
     * Получить режим ядра
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Запуск приложения
     * @return mixed
     * @throws CriticalException
     */
    public function run()
    {
        if($this->exceptionHandler instanceof ExceptionsHandler){
            set_exception_handler($this->exceptionHandler);
        }
        $router = $this->router;
        if ($router instanceof Router) {
            return $router($this);
        }
        throw new CriticalException('Маршрутизатор не сконфигурирован');
    }

}