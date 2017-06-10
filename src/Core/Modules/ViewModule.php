<?php

namespace Core\Modules;

use Core\WebApp;
use Core\Module;
use Template\View;

class ViewModule implements Module
{

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        $app['view'] = $app->factory(function () {
            return function ($templateDir, $templateExt = '.php') {
                return new View($templateDir, $templateExt);
            };
        });
    }
}