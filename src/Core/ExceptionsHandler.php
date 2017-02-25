<?php

namespace Core;

use Core\Exceptions\CriticalException;
use Core\Interfaces\Application;
use Core\Interfaces\ExceptionsHandler as ExceptionsHandlerInterface;

class ExceptionsHandler implements ExceptionsHandlerInterface
{

    /**
     * Маршруты обработки исключений,
     * где ключ - имя класса исключения, которое необходимо обрабатать
     * значение - функция или метод класса, которое обрабатывает исключение
     * @return array
     */
    public function getRoutsHandlers()
    {
        return [];
    }

    /**
     * Обработка ошибок
     * @param \Exception $exception
     * @param Application $application
     * @return void
     * @throws CriticalException
     */
    public function __invoke(\Exception $exception, Application $application)
    {
        $handlers = $this->getRoutsHandlers();
        foreach ($handlers as $key => $handler) {
            if($exception instanceof $key and is_callable($handler)){
                $handler($exception, $application);
                return;
            }
        }
        throw new CriticalException('Обработчик исключения не определен');
    }
}