<?php

namespace Core\Interfaces;

interface Command
{

    /**
     * Выполнить команду над моделью
     * @return boolean
     */
    public function execute();
}