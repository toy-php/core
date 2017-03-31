<?php

namespace Core\App\Queries;

use Core\Bus\Interfaces\Query;

class Routs implements Query
{

    protected $routs = [];
    protected $suffix = '';
    protected $queryString = '';

    public function __construct(array $routs, $queryString, $suffix)
    {
        $this->routs = $routs;
        $this->suffix = $suffix;
        $this->queryString = $queryString;
    }

    /**
     * получить суффикс для ЧПУ
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Получить маршруты
     * @return array
     */
    public function getRouts()
    {
        return $this->routs;
    }

    /**
     * Получить строку http запроса
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }


}