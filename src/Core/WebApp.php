<?php

namespace Core;

use Container\Container;
use Core\Modules\BusModule;
use Core\Modules\ConfigModule;
use Core\Modules\DataBaseModule;
use Core\Modules\FormModule;
use Core\Modules\HeadersModule;
use Core\Modules\RequestModule;
use Core\Modules\ResponseModule;
use Core\Modules\UriModule;
use Core\Modules\ViewModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Router\Router;
use Core\Modules\RouterModule as RouterModule;

class WebApp extends Container
{

    protected $responseProtocol = '1.1';
    protected $responseStatusCode = 200;
    protected $responseHeaders = [];

    public function __construct(array $config = [])
    {
        ob_start(null, 0,
            PHP_OUTPUT_HANDLER_CLEANABLE |
            PHP_OUTPUT_HANDLER_FLUSHABLE |
            PHP_OUTPUT_HANDLER_REMOVABLE
        );
        parent::__construct([], false);

        $this->registerModule(new BusModule());
        $this->registerModule(new ConfigModule($config));
        $this->registerModule(new DataBaseModule());
        $this->registerModule(new FormModule());
        $this->registerModule(new HeadersModule());
        $this->registerModule(new RequestModule());
        $this->registerModule(new ResponseModule(
            $this->responseProtocol,
            $this->responseStatusCode,
            $this->responseHeaders
        ));
        $this->registerModule(new RouterModule());
        $this->registerModule(new UriModule());
        $this->registerModule(new ViewModule());

    }

    /**
     * Регистрация модуля в ядре
     * @param Module $module
     */
    public function registerModule(Module $module)
    {
        $module->register($this);
    }

    /**
     * Проверка и получение необходимых компонент
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function required(array $params)
    {
        $result = [];
        foreach ($params as $name => $param) {
            switch (gettype($name)){
                case 'string':
                    if(!$this->offsetExists($name)){
                        throw new Exception(
                            sprintf('Необходимый компонент %s не зарегистрирован в ядре', $name)
                        );
                    }
                    $value = $this[$name];
                    if(!$value instanceof $param){
                        throw new Exception(
                            sprintf('Компонент %s не реализует необходимый интерфейс', $name)
                        );
                    }
                    $result[] = $value;
                    break;
                case 'integer':
                    if(!$this->offsetExists($param)){
                        throw new Exception(
                            sprintf('Необходимый компонент %s не зарегистрирован в ядре', $param)
                        );
                    }
                    $result[] = $this[$param];
                    break;
                default:
                    throw new Exception('Неверный тип');
            }
        }
        return $result;
    }

    /**
     * Вывод подготовленного ответа
     * @param ResponseInterface $response
     */
    protected function respond(ResponseInterface $response)
    {
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }
        $content = $response->getBody()->getContents();
        file_put_contents('php://output', $content);
    }

    /**
     * Запуск приложения
     */
    public function run()
    {
        try{

            list($router, $request, $response) = $this->required([
                'router' => Router::class,
                'request' => ServerRequestInterface::class,
                'response' => ResponseInterface::class
            ]);
            $response = $router->run($request, $response, $this);
            $this->respond($response);
        }catch (\Throwable $exception){
            ob_end_clean();
            throw $exception;
        }
    }
}