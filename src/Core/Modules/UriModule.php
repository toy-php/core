<?php

namespace Core\Modules;

use Core\WebApp;
use Core\Module;
use Http\Uri;

class UriModule implements Module
{

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        $app['uri'] = function () {
            $uri = new Uri();
            return $uri->withHost(filter_input(INPUT_SERVER, 'HTTP_HOST'))
                ->withScheme(trim(filter_input(INPUT_SERVER, 'HTTPS')) ? 'https' : 'http')
                ->withPath(\parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'))['path'])
                ->withPort(filter_input(INPUT_SERVER, 'SERVER_PORT'))
                ->withQuery(filter_input(INPUT_SERVER, 'QUERY_STRING'));
        };
    }
}