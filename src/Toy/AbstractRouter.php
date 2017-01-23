<?php

namespace Toy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRouter
{

    /**
     * Объект запроса
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * Объект ответа
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Суффикс для шаблона запроса
     * @var string
     */
    protected $suffix = '(.html|\/)*?';

    /**
     * Массив маршрутов
     * @var array
     */
    protected $routs = [];

    /**
     * AbstractRouter constructor.
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(ServerRequestInterface $request,
                                ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Ошибка 403
     */
    public function error403Action()
    {
        return $this->response->withStatus(404);
    }

    /**
     * Ошибка 404
     */
    public function error404Action()
    {
        return $this->response->withStatus(404);
    }

    /**
     * Ошибка 500
     */
    public function error500Action()
    {
        return $this->response->withStatus(500);
    }

    /**
     * Запуск маршрутизатора
     * @return ResponseInterface
     */
    public function __invoke()
    {
        if (empty($this->routs)) {
            return $this->error500Action();
        }
        $query = $this->request->getMethod() . $this->request->getUri()->getPath();
        $routs = $this->parseRouts($this->routs);
        foreach ($routs as $pattern => $action) {
            if (preg_match('#^' . $pattern . $this->suffix . '$#is', $query, $matches)) {
                array_shift($matches);
                $this->setRequestAttributes($matches);
                return $this->execute($action, $matches);
            }
        }
        return $this->response;
    }

    private function execute($action, $matches)
    {
        if (method_exists($this, $action)) {
            $result = $action($matches);
        } else {
            $result =  $this->error404Action();
        }
        if(!$result instanceof ResponseInterface){
            throw new \Exception('Метод обработчика маршрута не возвращает необходимый интерфейс');
        }
        return $result;
    }

    private function setRequestAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->request = $this->request->withAttribute($name, $value);
        }
    }

    private function parseRouts($routs, $group = '', &$result = [])
    {
        foreach ($routs as $pattern => $action) {
            if (is_array($action)) {
                $this->parseRouts($action, $group . $pattern, $result);
            }else{
                $pattern_array = explode('/', $pattern);
                $method = array_shift($pattern_array);
                $pattern_chunk = '/' . implode('/', $pattern_array);
                $result[$method . $group . $pattern_chunk] = $action;
            }
        }
        return $result;
    }

}