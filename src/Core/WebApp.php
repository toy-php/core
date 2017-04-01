<?php

namespace Core;

use Core\App\Commands\Dispatch;
use Core\App\Handlers\Dispatcher;
use Core\App\Handlers\HttpRequest;
use Core\App\Handlers\HttpResponse;
use Core\App\Handlers\Router;
use Core\App\Queries\GetHttpRequest;
use Core\App\Queries\GetHttpResponse;
use Core\App\Queries\Routs;
use Core\Exceptions\Http404Exception;
use Core\Locale\I18n;

class WebApp extends Module
{

    public function __construct($config = [])
    {
        $defaultConfig = [
            'http' => [
                'routs' => [],
                'url_suffix' => '(.html|\/)*?',
                'response_protocol' => '1.1',
                'response_status_code' => '200',
                'response_headers' => [],
            ]
        ];
        parent::__construct(array_merge($defaultConfig, $config));
        $this->queryBus->addHandler(Routs::class, Router::class);
        $this->queryBus->addHandler(GetHttpRequest::class, HttpRequest::class);
        $this->queryBus->addHandler(GetHttpResponse::class, HttpResponse::class);
        $this->commandBus->addHandler(Dispatch::class, Dispatcher::class);
    }

    /**
     * Запуск приложения
     */
    public function run()
    {
        $routs = $this->dependencyContainer['http']['routs'];
        $suffix = $this->dependencyContainer['http']['url_suffix'];
        $request = $this->commonBus->handle(new GetHttpRequest());
        $queryString = $request->getMethod() . $request->getUri()->getPath();
        $route = $this->commonBus->handle(new Routs($routs, $queryString, $suffix));
        if (!$route) {
            throw new Http404Exception(I18n::t('Маршрут недоступен'));
        }
        list($handler, $matches) = $route;
        $response = $this->commonBus->handle(new GetHttpResponse(
            $this->dependencyContainer['http']['response_protocol'],
            $this->dependencyContainer['http']['response_status_code'],
            $this->dependencyContainer['http']['response_headers']
        ));
        $this->commonBus->handle(new Dispatch($handler, $matches, $request, $response));
    }

}