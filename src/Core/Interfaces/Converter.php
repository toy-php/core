<?php

namespace Core\Interfaces;

interface Converter
{

    /**
     * Конвертировать модель в строку
     * @param Model $model
     * @return string
     */
    public function convert(Model $model);
}