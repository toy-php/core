<?php

namespace Core\Bus;

use Core\Bus\Interfaces\Bus;
use Core\Bus\Interfaces\Message;
use Core\Bus\Interfaces\Query;
use Core\Bus\Interfaces\QueryHandler;
use Core\Exceptions\CriticalException;

class QueryBus implements Bus
{

    protected $handlers;

    public function __construct()
    {
        $this->handlers = new \ArrayObject();
    }

    /**
     * Добавить обработчик запроса
     * @param $queryClass
     * @param $handlerClass
     * @throws CriticalException
     */
    public function addHandler($queryClass, $handlerClass)
    {
        if(!class_exists($queryClass)){
            throw new CriticalException('Класс запроса недоступен');
        }
        if(!class_exists($handlerClass)){
            throw new CriticalException('Класс обработчика запроса недоступен');
        }
        if(isset($this->handlers[$queryClass])){
            throw new CriticalException('Обработчик для данного запроса назначен');
        }
        $this->handlers[$queryClass] = $handlerClass;
    }

    /**
     * Получить обработчик запроса
     * @param Query $query
     * @return string|boolean
     */
    public function getHandlers(Query $query)
    {
        $queryType = get_class($query);
        return isset($this->handlers[$queryType]) ? $this->handlers[$queryType] : false;
    }

    /**
     * Обработать сообщение
     * @param Message $message
     * @return mixed
     * @throws CriticalException
     */
    public function handle(Message $message)
    {
        if(!$message instanceof Query){
            throw new CriticalException('Неверный тип сообщения');
        }
        $handlerClass = $this->getHandlers($message);
        $handler = new $handlerClass();
        if(!$handler instanceof QueryHandler){
            throw new CriticalException('Неверный тип обработчика');
        }
        return $handler->handle($message);
    }

}