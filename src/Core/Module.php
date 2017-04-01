<?php

namespace Core;

use Core\Bus\CommandBus;
use Core\Bus\CommonBus;
use Core\Bus\EventBus;
use Core\Bus\Interfaces\Command;
use Core\Bus\Interfaces\Event;
use Core\Bus\Interfaces\Query;
use Core\Bus\QueryBus;

class Module
{

    /**
     * Контейнер зависимостей
     * @var \ArrayAccess|Container
     */
    protected $dependencyContainer;

    /**
     * Шина событий
     * @var EventBus
     */
    protected $eventBus;

    /**
     * Шина запросов
     * @var QueryBus
     */
    protected $queryBus;

    /**
     * Шина команд
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * Общая шина
     * @var CommonBus
     */
    protected $commonBus;

    /**
     * Module constructor.
     * @param array|\ArrayAccess $config
     */
    public function __construct($config = [])
    {
        $this->dependencyContainer = ($config instanceof \ArrayAccess)
            ? $config
            : new Container($config);
        $this->eventBus = new EventBus();
        $this->queryBus = new QueryBus();
        $this->commandBus = new CommandBus();
        $this->commonBus = new CommonBus();
        $this->commonBus->route(Event::class, $this->eventBus);
        $this->commonBus->route(Query::class, $this->queryBus);
        $this->commonBus->route(Command::class, $this->commandBus);
    }

}