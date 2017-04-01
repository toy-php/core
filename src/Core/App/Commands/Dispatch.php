<?php

namespace Core\App\Commands;

use Core\Bus\Interfaces\Command;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatch implements Command
{

    protected $handler;
    protected $matches;
    protected $request;
    protected $response;

    public function __construct(
        callable $handler,
        array $matches,
        ServerRequestInterface $request,
        ResponseInterface $response)
    {
        $this->handler = $handler;
        $this->matches = $matches;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Обработчик запроса
     * @return callable
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Параметры строки запроса
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Запрос
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Ответ
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }


}