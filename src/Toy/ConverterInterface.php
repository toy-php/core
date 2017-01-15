<?php

namespace Toy;

interface ConverterInterface
{

    /**
     * Преобразование представления в строку
     * @param AbstractView $view
     * @return string
     */
    public function convert(AbstractView $view);
}