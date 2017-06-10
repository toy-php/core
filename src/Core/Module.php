<?php

namespace Core;

interface Module
{

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app);
}