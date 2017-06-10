<?php

namespace Core\Modules;

use Core\WebApp;
use Core\Module;
use Router\Router;

class RouterModule implements Module
{
    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        $app['router'] = function (WebApp $app) {
            /** @var ConfigModule $config */
            list($config) = $app->required(['config' => ConfigModule::class]);
            return new Router($config->get('router', []));
        };
    }
}