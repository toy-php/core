<?php

namespace Core\Modules;

use Core\Module;
use Core\WebApp;
use Forms\Form;
use Psr\Http\Message\ServerRequestInterface;

class FormModule implements Module
{

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        $app['form'] = $app->factory(function (WebApp $app){
            /** @var ServerRequestInterface $request */
            list($request) = $app->required([
                'request' => ServerRequestInterface::class
            ]);
            return new Form($request->getParsedBody());
        });
    }
}