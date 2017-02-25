<?php

namespace Core\Interfaces;

interface Value extends Model
{

    /**
     * Получить значение
     * @return mixed
     */
    public function getValue();
}