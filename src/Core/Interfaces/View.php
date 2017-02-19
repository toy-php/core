<?php

namespace Core\Interfaces;

interface View extends Observer
{

    /**
     * Получение экземпляра с необходимым преобразователем
     * @param Converter $converter
     * @return static
     */
    public function withConverter(Converter $converter);

    /**
     * Рендеринг
     * @return string
     */
    public function render();

}