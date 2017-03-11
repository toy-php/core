<?php

namespace Web;

use Core\Interfaces\Application;
use Core\Interfaces\Router as RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Web\Exceptions\HttpErrorException;

class Router implements RouterInterface
{

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Установка объекта запроса
     * @param ServerRequestInterface $request
     */
    private function setRequest( ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Установка объекта ответа
     * @param ResponseInterface $response
     */
    private function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Парсинг маршрутов
     * @param $routs
     * @param string $group
     * @param array $result
     * @return array
     */
    private function parseRouts($routs, $group = '', &$result = [])
    {
        foreach ($routs as $pattern => $action) {
            if (is_array($action) and !is_callable($action)) {
                $this->parseRouts(
                    $action,
                    $group . (is_string($pattern) ? $pattern : ''),
                    $result);
            } elseif(is_callable($action)) {
                $pattern_array = explode('/', $pattern);
                $method = array_shift($pattern_array);
                $pattern_chunk = '/' . ltrim(implode('/', $pattern_array), '/');
                $result[$method . $group . $pattern_chunk] = $action;
            }
        }
        return $result;
    }

    /**
     * Установка атрибутов запроса
     * @param array $attributes
     */
    private function setRequestAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->request = $this->request->withAttribute($name, $value);
        }
    }

    /**
     * Запуск маршрутизатора
     * @param Application $application
     * @return ResponseInterface
     * @throws HttpErrorException
     */
    public function __invoke(Application $application)
    {
        /** @var \Web\Application $application */
        $this->setRequest($application->getRequest());
        $this->setResponse($application->getResponse());
        $routs = $this->parseRouts($application->get('routs', []));
        $suffix = $application->get('friendly_url_suffix', '(.html|\/)*?');
        $query = $this->request->getMethod() . $this->request->getUri()->getPath();
        foreach ($routs as $pattern => $handler) {
            if (preg_match('#^' . rtrim($pattern, '/') . $suffix . '$#is', $query, $matches)
                and is_callable($handler)){
                array_shift($matches);
                $this->setRequestAttributes($matches);
                return $handler($this->request, $this->response, $application);
            }
        }
        throw new HttpErrorException('Маршрут не найден', 404);
    }
}