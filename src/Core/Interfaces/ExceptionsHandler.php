<?php

namespace Core\Interfaces;

interface ExceptionsHandler
{

    /**
     * Обработка ошибок
     * @param \Exception $exception
     * @param Application $application
     * @return void
     */
    public function __invoke(\Exception $exception, Application $application);
}