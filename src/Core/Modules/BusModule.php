<?php

namespace Core\Modules;

use Bus\CommandBus;
use Bus\CommonBus;
use Bus\EventBus;
use Bus\Interfaces\Command;
use Bus\Interfaces\Event;
use Bus\Interfaces\Message;
use Bus\Interfaces\Query;
use Bus\QueryBus;
use Core\WebApp;
use Core\Module;

class BusModule implements Module
{

    protected $queryBus;
    protected $commandBus;
    protected $eventBus;
    protected $commonBus;

    public function __construct()
    {
        $this->queryBus = new QueryBus();
        $this->commandBus = new CommandBus();
        $this->eventBus = new EventBus();
        $this->commonBus = new CommonBus();
        $this->commonBus->route(Query::class, $this->queryBus);
        $this->commonBus->route(Command::class, $this->commandBus);
        $this->commonBus->route(Event::class, $this->eventBus);
    }

    /**
     * Добавить в маршрутизатор сообщений, сообщение запроса и его обработчик
     * @param $message
     * @param $handler
     */
    public function routeQuery($message, $handler)
    {
        $this->queryBus->addHandler($message, $handler);
    }

    /**
     * Добавить в маршрутизатор сообщений, сообщение события и его обработчик
     * @param $message
     * @param $handler
     */
    public function routeEvent($message, $handler)
    {
        $this->eventBus->addHandler($message, $handler);
    }

    /**
     * Добавить в маршрутизатор сообщений, сообщение команды и его обработчик
     * @param $message
     * @param $handler
     */
    public function routeCommand($message, $handler)
    {
        $this->commandBus->addHandler($message, $handler);
    }

    /**
     * Обработать сообщение
     * @param Message $message
     * @return mixed
     */
    public function handleMessage(Message $message)
    {
        return $this->commonBus->handle($message);
    }

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        /** @var ConfigModule $config */
        list($config) = $app->required([
            'config' => ConfigModule::class
        ]);

        $busConfig = $config->get('bus', []);

        $commands = isset($busConfig['commands']) ? $busConfig['commands'] : [];
        foreach ($commands as $command => $handler) {
            $this->routeCommand($command, $handler);
        }

        $queries = isset($busConfig['queries']) ? $busConfig['queries'] : [];
        foreach ($queries as $query => $handler) {
            $this->routeQuery($query, $handler);
        }

        $events = isset($busConfig['events']) ? $busConfig['events'] : [];
        foreach ($events as $event => $handler) {
            $this->routeEvent($event, $handler);
        }

        $app['bus'] = $this;

    }
}