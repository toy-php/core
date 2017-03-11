<?php

namespace Core\Interfaces;

interface ExceptionsHandler
{

    /**
     * Обработка ошибок
     * @param \Throwable $exception
     * @return void
     */
    public function __invoke(\Throwable $exception);
}