<?php

namespace Core\Interfaces;

interface Router
{

    /**
     * Запуск маршрутизатора
     * @param Application $application
     * @return void
     */
    public function __invoke(Application $application);

}