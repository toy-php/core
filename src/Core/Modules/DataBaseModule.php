<?php

namespace Core\Modules;

use Core\WebApp;
use Core\Module;
use DB\DataBase;

class DataBaseModule implements Module
{

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        $app['db'] = $app->factory(function () {
            return function ($dsn, $username = null, $password = null, $options = []) {
                return new DataBase($dsn, $username, $password, $options);
            };
        });
    }
}