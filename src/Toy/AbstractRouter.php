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
     * Модель
     * @var AbstractModel
     */
    protected $model;

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
     * @param AbstractModel $model
     */
    public function __construct(ServerRequestInterface $request,
                                ResponseInterface $response,
                                AbstractModel $model)
    {
        $this->request = $request;
        $this->response = $response;
        $this->model = $model;
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
                if (method_exists($this, $action)) {
                    $this->setRequestAttributes($matches);
                    return call_user_func_array([$this, $action], $matches);
                } else {
                    return $this->error404Action();
                }
            }
        }
        return $this->response;
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