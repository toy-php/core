<?php

namespace Core;

use Core\Exceptions\CriticalException;
use Core\Interfaces\Application;
use Core\Interfaces\ExceptionsHandler as ExceptionsHandlerInterface;

class ExceptionsHandler implements ExceptionsHandlerInterface
{

    /**
     * @var Application
     */
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

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
     * @param \Throwable $exception
     * @return void
     * @throws CriticalException
     */
    public function __invoke(\Throwable $exception)
    {
        try{
            $handlers = $this->getRoutsHandlers();
            foreach ($handlers as $key => $handler) {
                if ($exception instanceof $key and is_callable($handler)) {
                    $handler($exception);
                    return;
                }
            }
            throw new CriticalException('Обработчик исключения не определен');
        }catch (CriticalException $exception){
            echo $exception->getMessage();
        }
    }
}