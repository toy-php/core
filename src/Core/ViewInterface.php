<?php

namespace Core;

interface ViewInterface extends \ArrayAccess
{

    /**
     * Преобразование структуры представления в массив
     * @return array
     */
    public function toArray();

}