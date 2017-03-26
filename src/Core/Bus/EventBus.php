<?php

namespace Core\Bus;

use Core\Bus\Interfaces\Bus;
use Core\Bus\Interfaces\Event;
use Core\Bus\Interfaces\EventHandler;
use Core\Bus\Interfaces\Message;
use Core\Exceptions\CriticalException;

class EventBus implements Bus
{

    protected $handlers;
    protected $dependencyContainer;

    public function __construct(\ArrayAccess $dependencyContainer)
    {
        $this->handlers = new \ArrayObject();
        $this->dependencyContainer = $dependencyContainer;
    }

    /**
     * Добавить обработчик события
     * @param $eventClass
     * @param $handlerClass
     * @throws CriticalException
     */
    public function addHandler($eventClass, $handlerClass)
    {
        if(!class_exists($eventClass)){
            throw new CriticalException('Класс события недоступен');
        }
        if(!class_exists($handlerClass)){
            throw new CriticalException('Класс обработчика события недоступен');
        }
        if(!isset($this->handlers[$eventClass])){
            $this->handlers[$eventClass] = [];
        }
        $this->handlers[$eventClass][] = $handlerClass;
    }

    /**
     * Получить массив обработчиков
     * @param Event $event
     * @return array
     */
    public function getHandlers(Event $event)
    {
        $eventType = get_class($event);
        return isset($this->handlers[$eventType]) ? $this->handlers[$eventType] : [];
    }

    /**
     * Обработать сообщение
     * @param Message $message
     * @return void
     * @throws CriticalException
     */
    public function handle(Message $message)
    {
        if(!$message instanceof Event){
            throw new CriticalException('Неверный тип сообщения');
        }
        $handlers = $this->getHandlers($message);
        foreach ($handlers as $handlerClass){
            $handler = new $handlerClass($this->dependencyContainer);
            if(!$handler instanceof EventHandler){
                throw new CriticalException('Неверный тип обработчика');
            }
            $handler->handle($message);
        }
    }
}