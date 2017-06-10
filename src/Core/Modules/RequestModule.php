<?php

namespace Core\Modules;

use Core\WebApp;
use Core\Module;
use Http\ServerRequest;
use Http\Stream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RequestModule implements Module
{

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        $app['request'] = function (WebApp $app) {
            list($headers, $uri) = $app->required([
                'headers',
                'uri' => UriInterface::class
            ]);
            /** @var ServerRequestInterface $request */
            $request = new ServerRequest($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);
            if (!empty($headers)) {
                foreach ($headers as $name => $value) {
                    $request = $request->withHeader($name, $value);
                }
            }
            $request = $request
                ->withMethod(filter_input(INPUT_SERVER, 'REQUEST_METHOD'))
                ->withUri($uri)
                ->withBody(new Stream(fopen('php://input', 'r')));
            return $request;
        };
    }
}