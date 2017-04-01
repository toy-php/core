<?php

namespace Core\App\Handlers;

use Core\App\Http\ServerRequest;
use Core\App\Http\Stream;
use Core\App\Http\Uri;
use Core\Bus\Interfaces\Message;
use Core\Bus\Interfaces\QueryHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class HttpRequest implements QueryHandler
{

    protected static $request;

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

    /**
     * Обработать запрос
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message)
    {
        return !empty(static::$request) ? static::$request : static::$request = $this->buildRequest();
    }
}