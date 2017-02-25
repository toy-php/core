<?php

namespace Core\Interfaces;

interface Application extends Module
{

    /**
     * Получить режим работы ядра
     * @return mixed
     */
    public function getMode();
}