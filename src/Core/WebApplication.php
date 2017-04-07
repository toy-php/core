<?php

namespace Core;

use Core\Bus\CommandBus;
use Core\Bus\CommonBus;
use Core\Bus\EventBus;
use Core\Bus\Interfaces\Command;
use Core\Bus\Interfaces\Event;
use Core\Bus\Interfaces\Message;
use Core\Bus\Interfaces\Query;
use Core\Bus\QueryBus;
use Core\Container\Container;
use Core\DataMapper\ExtPDO;
use Core\Exceptions\Http404Exception;
use Core\Http\Response;
use Core\Http\ServerRequest;
use Core\Http\Stream;
use Core\Http\Uri;
use Core\Template\Template;
use Core\Throwable\Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class WebApplication extends Container
{

    /**
     * Режим работы ядра - разработка
     */
    const MODE_DEV = 1;

    /**
     * Режим работы ядра - продакшн
     */
    const MODE_PROD = 2;

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
     * Объект обработки ошибок
     * @var Throwable
     */
    protected $throwable;

    public function __construct(array $config = [])
    {
        $defaultConfig = [
            'mode' => static::MODE_DEV,
            'http' => [
                'routs' => [],
                'suffix' => '(.html|\/)*?',
                'response_protocol' => '1.1',
                'response_status_code' => '200',
                'response_headers' => [],
            ],
            'template' => [
                'config' => [
                    'dir' => '',
                    'file_ext' => '.php',
                    'functions' => [],
                    'vars' => []
                ],
                'parser' => function($config = []){
                    return new Template($config);
                }
            ],
            'db' => [
                'config' => [
                    'dsn' => '',
                    'username' => '',
                    'password' => '',
                    'options' => '',
                ],
                'pdo' => function ($config) {
                    return new ExtPDO(
                        $config['dsn'],
                        $config['username'],
                        $config['password'],
                        $config['options']
                    );
                }
            ],
            'request' => function () {
                return $this->buildRequest();
            },
            'response' => function () {
                return $this->buildResponse();
            },
            'uri' => function () {
                return $this->buildUri();
            },
            'bus' => [
                'events' => function(EventBus $bus){

                },
                'commands' => function(CommandBus $bus){

                },
                'queries' => function(QueryBus $bus){

                }
            ]
        ];
        $config = array_replace_recursive($defaultConfig, $config);
        parent::__construct($config);
        $this->throwable = new Throwable($this);
        $this->eventBus = new EventBus();
        $this->queryBus = new QueryBus();
        $this->commandBus = new CommandBus();
        $this->commonBus = new CommonBus();
        $this->commonBus->route(Event::class, $this->eventBus);
        $this->commonBus->route(Query::class, $this->queryBus);
        $this->commonBus->route(Command::class, $this->commandBus);
        $this['bus']['events']($this->eventBus);
        $this['bus']['commands']($this->commandBus);
        $this['bus']['queries']($this->queryBus);
    }

    /**
     * Получить объект PDO
     * @param array $config
     * @return ExtPDO|\PDO
     */
    public function getPdo($config = [])
    {
        $config = array_replace_recursive($this['db']['config'], $config);
        return $this['db']['pdo']($config);
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

    /**
     * Добавить обработчик сообщение события
     * @param $eventClass
     * @param $handlerClass
     */
    public function addEventHandler($eventClass, $handlerClass)
    {
        $this->queryBus->addHandler($eventClass, $handlerClass);
    }

    /**
     * Добавить обработчик сообщение команды
     * @param $commandClass
     * @param $handlerClass
     */
    public function addCommandHandler($commandClass, $handlerClass)
    {
        $this->commandBus->addHandler($commandClass, $handlerClass);
    }

    /**
     * Добавить обработчик сообщение запроса
     * @param $queryClass
     * @param $handlerClass
     */
    public function addQueryHandler($queryClass, $handlerClass)
    {
        $this->queryBus->addHandler($queryClass, $handlerClass);
    }

    /**
     * Обработать сообщение
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message)
    {
        return $this->commonBus->handle($message);
    }

    /**
     * Получить объект шаблонизатора
     * @param array $config
     * @return Template
     */
    public function getTemplate($config = [])
    {
        $config = array_replace_recursive($this['template']['config'], $config);
        return $this['template']['parser']($config);
    }

    /**
     * Парсинг маршрутов
     * @param $routs
     * @param string $group
     * @param array $result
     * @return array
     */
    protected function parseRouts($routs, $group = '', &$result = [])
    {
        foreach ($routs as $pattern => $action) {
            if (is_array($action) and !is_callable($action)) {
                $this->parseRouts(
                    $action,
                    $group . (is_string($pattern) ? $pattern : ''),
                    $result);
            } elseif (is_callable($action)) {
                $pattern_array = explode('/', $pattern);
                $method = array_shift($pattern_array);
                $pattern_chunk = '/' . ltrim(implode('/', $pattern_array), '/');
                $result[$method . $group . $pattern_chunk] = $action;
            }
        }
        return $result;
    }

    /**
     * Установка атрибутов запроса
     * @param ServerRequestInterface $request
     * @param array $attributes
     * @return ServerRequestInterface
     */
    protected function addAttributes(ServerRequestInterface $request, array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }
        return $request;
    }

    /**
     * Конфигурирование ответа
     * @return ResponseInterface
     */
    protected function buildResponse()
    {
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
    }

    /**
     * Конфигурирование запроса
     * @return ServerRequestInterface
     */
    protected function buildRequest()
    {
        /** @var ServerRequestInterface $request */
        $request = new ServerRequest($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);
        $headers = $this->getHeaders();
        if (!empty($headers)) {
            foreach ($headers as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }
        $request = $request
            ->withMethod(filter_input(INPUT_SERVER, 'REQUEST_METHOD'))
            ->withUri($this->buildUri())
            ->withBody(new Stream(fopen('php://input', 'r')));
        return $request;
    }

    /**
     * Конфигурирование Uri
     * @return UriInterface
     */
    protected function buildUri()
    {
        $uri = new Uri();
        return $uri->withHost(filter_input(INPUT_SERVER, 'HTTP_HOST'))
            ->withScheme(trim(filter_input(INPUT_SERVER, 'HTTPS')) ? 'https' : 'http')
            ->withPath(\parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'))['path'])
            ->withPort(filter_input(INPUT_SERVER, 'SERVER_PORT'))
            ->withQuery(filter_input(INPUT_SERVER, 'QUERY_STRING'));
    }

    /**
     * Получение заголовков запроса
     * @return array|false
     */
    protected function getHeaders()
    {
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
     * @param bool $silent
     * @return mixed
     * @throws \Throwable
     */
    public function run($silent = true)
    {
        try{
            $request = $this->getRequest();
            $response = $this->getResponse();
            $queryString = $request->getMethod() . $request->getUri()->getPath();
            $routs = $this->parseRouts($this['http']['routs']);
            foreach ($routs as $pattern => $handler) {
                if (preg_match(
                        '#^' . rtrim($pattern, '/') . $this['http']['suffix'] . '$#is',
                        $queryString,
                        $matches
                    ) and is_callable($handler)
                ) {
                    array_shift($matches);
                    $request = $this->addAttributes($request, $matches);
                    $result = $handler($request, $response, $this);
                    if ($silent) {
                        $this->respond($result);
                    }
                    return $result;
                }
            }
            throw new Http404Exception('Маршрут не найден');
        }catch (\Throwable $exception){
            if($this['mode'] == static::MODE_DEV){
                $this->throwable->handle($exception);
            }else{
                throw $exception;
            }
        }
        return null;
    }
}