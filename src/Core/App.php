<?php

namespace Core;

use Core\App\Handlers\Router;
use Core\App\Handlers\ViewHandler;
use Core\App\Http\Response;
use Core\App\Http\ServerRequest;
use Core\App\Http\Stream;
use Core\App\Http\Uri;
use Core\App\Queries\Routs;
use Core\App\Queries\View;
use Core\Exceptions\HttpException;
use Core\Locale\I18n;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class App extends Module
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
            ],
            'template' => [
                'extends' => []
            ]
        ];
        parent::__construct(array_merge($defaultConfig, $config));
        $this->queryBus->addHandler(Routs::class, Router::class);
        $this->queryBus->addHandler(View::class, ViewHandler::class);
    }

    /**
     * Рендеринг шаблона
     * @param string $templateDir
     * @param string $templateName
     * @param array|object $data
     * @return string
     */
    public function render($templateDir, $templateName, $data)
    {
        $templateExt = '.php';
        $extends = $this->dependencyContainer['template']['extends'];
        return $this->commonBus->handle(
            new View(
                $data,
                rtrim($templateDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
                rtrim($templateName, $templateExt),
                $templateExt,
                $extends
            )
        );
    }

    /**
     * Запуск приложения
     */
    public function run()
    {
        $routs = $this->dependencyContainer['http']['routs'];
        $suffix = $this->dependencyContainer['http']['url_suffix'];
        $request = $this->buildRequest();
        $queryString = $request->getMethod() . $request->getUri()->getPath();
        $route = $this->commonBus->handle(new Routs($routs, $queryString, $suffix));
        if (!$route) {
            throw new HttpException(I18n::t('Маршрут недоступен'));
        }
        list($handler, $matches) = $route;
        $request = $this->addAttributes($request, $matches);
        $response = $this->buildResponse();
        $this->respond($handler($request, $response, $this));
    }

    /**
     * Вывод подготовленного ответа
     * @param ResponseInterface $response
     */
    private function respond(ResponseInterface $response)
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
     * Установка атрибутов запроса
     * @param ServerRequestInterface $request
     * @param array $attributes
     * @return ServerRequestInterface
     */
    private function addAttributes(ServerRequestInterface $request, array $attributes)
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
    private function buildResponse()
    {
        /** @var ResponseInterface $response */
        $response = (new Response())
            ->withBody(new Stream(fopen('php://memory', 'a')))
            ->withProtocolVersion($this->dependencyContainer['http']['response_protocol'])
            ->withStatus($this->dependencyContainer['http']['response_status_code']);
        $responseHeaders = $this->dependencyContainer['http']['response_headers'];
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
    private function buildRequest()
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
            ->withUri($this->createUri())
            ->withBody(new Stream(fopen('php://input', 'r')));
        return $request;
    }

    /**
     * Конфигурирование Uri
     * @return UriInterface
     */
    private function createUri()
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
    private function getHeaders()
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
}