<?php

namespace Core\Bus;

use Core\Bus\Interfaces\Bus;
use Core\Bus\Interfaces\Command;
use Core\Bus\Interfaces\CommandHandler;
use Core\Bus\Interfaces\Message;
use Core\Exceptions\CriticalException;

class CommandBus implements Bus
{

    protected $handlers;
    protected $dependencyContainer;

    public function __construct(\ArrayAccess $dependencyContainer)
    {
        $this->handlers = new \ArrayObject();
        $this->dependencyContainer = $dependencyContainer;
    }

    /**
     * Добавить обработчик команды
     * @param $commandClass
     * @param $handlerClass
     * @throws CriticalException
     */
    public function addHandler($commandClass, $handlerClass)
    {
        if(!class_exists($commandClass)){
            throw new CriticalException('Класс команды недоступен');
        }
        if(!class_exists($handlerClass)){
            throw new CriticalException('Класс обработчика команды недоступен');
        }
        if(isset($this->handlers[$commandClass])){
            throw new CriticalException('Обработчик для данной команды назначен');
        }
        $this->handlers[$commandClass] = $handlerClass;
    }

    /**
     * Получить обработчик команды
     * @param Command $command
     * @return string|boolean
     */
    public function getHandlers(Command $command)
    {
        $commandType = get_class($command);
        return isset($this->handlers[$commandType]) ? $this->handlers[$commandType] : false;
    }

    /**
     * Обработать сообщение
     * @param Message $message
     * @return boolean
     * @throws CriticalException
     */
    public function handle(Message $message)
    {
        if(!$message instanceof Command){
            throw new CriticalException('Неверный тип сообщения');
        }
        $handlerClass = $this->getHandlers($message);
        $handler = new $handlerClass($this->dependencyContainer);
        if(!$handler instanceof CommandHandler){
            throw new CriticalException('Неверный тип обработчика');
        }
        return $handler->handle($message);
    }
}