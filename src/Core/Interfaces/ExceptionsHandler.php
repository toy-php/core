<?php

namespace Core\Interfaces;

interface ExceptionsHandler
{

    /**
     * Обработка ошибок
     * @param \Exception $exception
     * @param null $mode
     * @return void
     */
    public function __invoke(\Exception $exception, $mode = null);
}