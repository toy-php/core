<?php

namespace Core\App\Queries;

use Core\Bus\Interfaces\Query;

class GetHttpResponse implements Query
{

    protected $responseProtocol;
    protected $responseStatusCode;
    protected $responseHeaders;

    public function __construct($responseProtocol, $responseStatusCode, array $responseHeaders)
    {
        $this->responseProtocol = $responseProtocol;
        $this->responseStatusCode = $responseStatusCode;
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * Получить протокол ответа
     * @return string
     */
    public function getResponseProtocol()
    {
        return $this->responseProtocol;
    }

    /**
     * Получить статус код ответа
     * @return string
     */
    public function getResponseStatusCode()
    {
        return $this->responseStatusCode;
    }

    /**
     * Получить заголовки ответа
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }


}