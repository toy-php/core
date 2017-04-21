<?php

namespace Core;

use Bus\CommandBus;
use Bus\CommonBus;
use Bus\EventBus;
use Bus\Interfaces\Command;
use Bus\Interfaces\Event;
use Bus\Interfaces\Message;
use Bus\Interfaces\Query;
use Bus\QueryBus;
use Container\Container;
use Core\Throwable\Throwable;
use DB\DataBase;
use Http\Response;
use Http\ServerRequest;
use Http\Stream;
use Http\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Router\Router;
use Template\View;

class Toy extends Container
{

    /**
     * Toy constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        ob_start(null, 0,
            PHP_OUTPUT_HANDLER_CLEANABLE |
            PHP_OUTPUT_HANDLER_FLUSHABLE |
            PHP_OUTPUT_HANDLER_REMOVABLE
        );
        parent::__construct([], false);
        $components = $this->getDefaultComponents();
        $config = array_replace_recursive($components, $config);
        foreach ($config as $key => $value) {
            $this[$key] = $value;
        }
        $throwable = new Throwable(
            $this->getRequest(),
            $this->getView(__DIR__ . '/Throwable/template/')
        );
        set_exception_handler($throwable);
    }

    /**
     * Вывод подготовленного ответа
     * @param ResponseInterface $response
     */
    protected function respond(ResponseInterface $response)
    {
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }
        $content = $response->getBody()->getContents();
        file_put_contents('php://output', $content);
    }

    /**
     * Запуск приложения
     */
    public function run()
    {
        try{
            /** @var Router $router */
            $router = $this['router'];
            $response = $router->run($this->getRequest(), $this->getResponse(), $this);
            $this->respond($response);
        }catch (\Throwable $exception){
            ob_end_clean();
            throw $exception;
        }
    }

    /**
     * Добавить в маршрутизатор сообщений, сообщение запроса и его обработчик
     * @param $message
     * @param $handler
     */
    public function routeQuery($message, $handler)
    {
        /** @var QueryBus $bus */
        $bus = $this['queryBus'];
        $bus->addHandler($message, $handler);
    }

    /**
     * Добавить в маршрутизатор сообщений, сообщение события и его обработчик
     * @param $message
     * @param $handler
     */
    public function routeEvent($message, $handler)
    {
        /** @var EventBus $bus */
        $bus = $this['eventBus'];
        $bus->addHandler($message, $handler);
    }

    /**
     * Добавить в маршрутизатор сообщений, сообщение команды и его обработчик
     * @param $message
     * @param $handler
     */
    public function routeCommand($message, $handler)
    {
        /** @var CommandBus $bus */
        $bus = $this['commandBus'];
        $bus->addHandler($message, $handler);
    }

    /**
     * Обработать сообщение
     * @param Message $message
     * @return mixed
     */
    public function handleMessage(Message $message)
    {
        /** @var CommonBus $bus */
        $bus = $this['bus'];
        return $bus->handle($message);
    }

    /**
     * Получить объект представления
     * @param $templateDir
     * @param string $templateExt
     * @return \Template\Interfaces\View
     */
    public function getView($templateDir, $templateExt = '.php')
    {
        return $this['view']($templateDir, $templateExt);
    }

    /**
     * Получить объект Uri
     * @return UriInterface
     */
    public function getUri()
    {
        return $this['uri'];
    }

    /**
     * Получить объект запроса
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this['request'];
    }

    /**
     * Получить объект ответа
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this['response'];
    }

    protected function getDefaultComponents()
    {
        return [
            'http' => [
                'routs' => [
                    'preRouts' => [],
                    'routs' => [],
                    'suffix' => '(.html|\/)*?',
                ],
                'response_protocol' => '1.1',
                'response_status_code' => '200',
                'response_headers' => [],
            ],
            'headers' => function () {
                if (!function_exists('apache_request_headers')) {
                    $arh = array();
                    $rx_http = '/\AHTTP_/';
                    foreach ($_SERVER as $key => $val) {
                        if (preg_match($rx_http, $key)) {
                            $arh_key = preg_replace($rx_http, '', $key);
                            $rx_matches = explode('_', $arh_key);
                            if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                                foreach ($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                                $arh_key = implode('-', $rx_matches);
                            }
                            $arh[$arh_key] = $val;
                        }
                    }
                    return ($arh);
                }
                return apache_request_headers();
            },
            'uri' => function () {
                $uri = new Uri();
                return $uri->withHost(filter_input(INPUT_SERVER, 'HTTP_HOST'))
                    ->withScheme(trim(filter_input(INPUT_SERVER, 'HTTPS')) ? 'https' : 'http')
                    ->withPath(\parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'))['path'])
                    ->withPort(filter_input(INPUT_SERVER, 'SERVER_PORT'))
                    ->withQuery(filter_input(INPUT_SERVER, 'QUERY_STRING'));
            },
            'request' => function (Toy $app) {
                /** @var ServerRequestInterface $request */
                $request = new ServerRequest($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);
                $headers = $app['headers'];
                if (!empty($headers)) {
                    foreach ($headers as $name => $value) {
                        $request = $request->withHeader($name, $value);
                    }
                }
                $request = $request
                    ->withMethod(filter_input(INPUT_SERVER, 'REQUEST_METHOD'))
                    ->withUri($app['uri'])
                    ->withBody(new Stream(fopen('php://input', 'r')));
                return $request;
            },
            'response' => function () {
                /** @var ResponseInterface $response */
                $response = (new Response())
                    ->withBody(new Stream(fopen('php://memory', 'a')))
                    ->withProtocolVersion($this['http']['response_protocol'])
                    ->withStatus($this['http']['response_status_code']);
                $responseHeaders = $this['http']['response_headers'];
                if (!empty($responseHeaders)) {
                    foreach ($responseHeaders as $name => $value) {
                        $response = $response->withHeader($name, $value);
                    }
                }
                return $response;
            },
            'router' => function (Toy $app) {
                return new Router($app['http']['routs']);
            },
            'view' => $this->factory(function () {
                return function ($templateDir, $templateExt = '.php') {
                    return new View($templateDir, $templateExt);
                };
            }),
            'db' => $this->factory(function () {
                return function ($dsn, $username = null, $password = null, $options = []) {
                    return new DataBase($dsn, $username, $password, $options);
                };
            }),
            'queryBus' => function () {
                return new QueryBus();
            },
            'commandBus' => function () {
                return new CommandBus();
            },
            'eventBus' => function () {
                return new EventBus();
            },
            'bus' => function (Toy $app) {
                $bus = new CommonBus();
                $bus->route(Query::class, $app['queryBus']);
                $bus->route(Command::class, $app['commandBus']);
                $bus->route(Event::class, $app['eventBus']);
                return $bus;
            }
        ];
    }
}