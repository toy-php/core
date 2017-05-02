<?php

namespace Core;

interface Module
{

    /**
     * Регистрация модуля в ядре
     * @param Toy $core
     * @return void
     */
    public function register(Toy $core);
}